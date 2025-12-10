<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Booking.php";
require_once __DIR__ . "/../../models/BlockedDate.php";
require_once __DIR__ . "/../../config/auth.php";

require_admin(); // Cek role admin di sini

class BookingController
{
    private $conn;
    private $booking;
    private $blocked;

    public function __construct()
    {
        // Mengambil koneksi PDO dari Singleton
        $this->conn = Database::getInstance();

        // Model Booking mengambil koneksi di constructor-nya
        $this->booking = new Booking(); 
        // Model BlockedDate menerima koneksi di constructor-nya
        $this->blocked = new BlockedDate($this->conn);
    }

    public function createBooking($data)
    {
        $sarana_id = $data['sarana_id'];
        $tanggal   = $data['tanggal'];
        $start     = $data['start_time'];
        $end       = $data['end_time'];

        if ($this->blocked->isBlockedRange($sarana_id, $tanggal, $start, $end)) {
            return ['success' => false, 'message' => 'Waktu ini diblok oleh admin.'];
        }

        if ($this->booking->hasConflict($sarana_id, $tanggal, $start, $end)) {
            return ['success' => false, 'message' => 'Bentrok dengan booking lain.'];
        }

        $booking_id = $this->booking->create($data);

        return [
            'success' => true,
            'message' => 'Booking berhasil dibuat.',
            'booking_id' => $booking_id
        ];
    }

    public function approveBooking($booking_id, $admin_id)
    {
        $query = "UPDATE bookings SET status = 'disetujui', handled_by = :admin, updated_at = NOW() WHERE booking_id = :id AND status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $ok = $stmt->execute([":admin" => $admin_id, ":id" => $booking_id]);
        
        if ($ok && $stmt->rowCount() > 0) {
             return ['success' => true, 'message' => "Booking berhasil disetujui."];
        } else {
             // Jika rowCount 0, berarti ID tidak ditemukan atau status bukan 'pending'
             return ['success' => false, 'message' => "Gagal menyetujui booking (ID tidak ditemukan atau sudah diproses)."];
        }
    }

    public function rejectBooking($booking_id, $admin_id, $reason)
    {
        $query = "UPDATE bookings SET status = 'ditolak', rejection_reason = :reason, handled_by = :admin, updated_at = NOW() WHERE booking_id = :id AND status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $ok = $stmt->execute([":reason" => $reason, ":admin" => $admin_id, ":id" => $booking_id]);

        if ($ok && $stmt->rowCount() > 0) {
             return ['success' => true, 'message' => "Booking berhasil ditolak."];
        } else {
             return ['success' => false, 'message' => "Gagal menolak booking (ID tidak ditemukan atau sudah diproses)."];
        }
    }

}
?>