<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hapus header content-type di sini karena ini class library, bukan output JSON langsung
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Booking.php";
require_once __DIR__ . "/../../models/BlockedDate.php";
require_once __DIR__ . "/../../config/auth.php";

// --- HAPUS require_admin() DARI SINI AGAR MAHASISWA BISA BOOKING ---

class BookingController
{
    private $conn;
    private $booking;
    private $blocked;

    public function __construct()
    {
        // Gunakan Singleton agar konsisten
        $this->conn = Database::getInstance();

        // Pastikan Model Booking menggunakan koneksi Singleton juga
        $this->booking = new Booking(); 
        
        // BlockedDate biasanya butuh koneksi dilempar
        $this->blocked = new BlockedDate($this->conn);
    }

    public function createBooking($data)
    {
        $sarana_id = $data['sarana_id'];
        $tanggal   = $data['tanggal'];
        $start     = $data['start_time'];
        $end       = $data['end_time'];

        // Cek apakah tanggal diblokir admin
        if ($this->blocked->isBlockedRange($sarana_id, $tanggal, $start, $end)) {
            return ['success' => false, 'message' => 'Waktu ini diblokir oleh admin.'];
        }

        // Cek bentrok jadwal
        if ($this->booking->hasConflict($sarana_id, $tanggal, $start, $end)) {
            return ['success' => false, 'message' => 'Jadwal bentrok dengan booking lain.'];
        }

        $booking_id = $this->booking->create($data);

        if ($booking_id) {
            return [
                'success' => true,
                'message' => 'Booking berhasil diajukan.',
                'booking_id' => $booking_id
            ];
        } else {
            return ['success' => false, 'message' => 'Gagal menyimpan data booking.'];
        }
    }

    public function approveBooking($booking_id, $admin_id)
    {
        // Cek status harus pending agar tidak double approve
        $query = "UPDATE bookings SET status = 'disetujui', handled_by = :admin, updated_at = NOW() WHERE booking_id = :id";
        $stmt = $this->conn->prepare($query);
        $ok = $stmt->execute([":admin" => $admin_id, ":id" => $booking_id]);
        
        if ($ok) {
             return ['success' => true, 'message' => "Booking berhasil disetujui."];
        } else {
             return ['success' => false, 'message' => "Gagal menyetujui booking."];
        }
    }

    public function rejectBooking($booking_id, $admin_id, $reason)
    {
        $query = "UPDATE bookings SET status = 'ditolak', rejection_reason = :reason, handled_by = :admin, updated_at = NOW() WHERE booking_id = :id";
        $stmt = $this->conn->prepare($query);
        $ok = $stmt->execute([":reason" => $reason, ":admin" => $admin_id, ":id" => $booking_id]);

        if ($ok) {
             return ['success' => true, 'message' => "Booking berhasil ditolak."];
        } else {
             return ['success' => false, 'message' => "Gagal menolak booking."];
        }
    }
}
?>