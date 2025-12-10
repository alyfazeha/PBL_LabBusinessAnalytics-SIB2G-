<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Booking.php";
require_once __DIR__ . "/../../models/BlockedDate.php";
require_once __DIR__ . "/../../config/auth.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class BookingController
{
    private $conn;
    private $booking;
    private $blocked;

    public function __construct()
    {
        // PERBAIKAN: Gunakan Singleton agar tidak crash
        $this->conn = Database::getInstance();

        $this->booking = new Booking();
        // Model BlockedDate butuh koneksi, kita kirim koneksi singleton
        $this->blocked = new BlockedDate($this->conn);
    }

    public function createBooking($data)
    {
        // Cek apakah user login
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'Akses ditolak. Silakan login.'];
        }

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

        return [
            'success' => true,
            'message' => 'Booking berhasil dibuat.',
            'booking_id' => $booking_id
        ];
    }

    public function approveBooking($booking_id, $admin_id)
    {
        $query = "UPDATE bookings SET status = 'disetujui', handled_by = :admin, updated_at = NOW() WHERE booking_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([":admin" => $admin_id, ":id" => $booking_id]);

        return ['success' => true, 'message' => "Booking berhasil disetujui."];
    }

    public function rejectBooking($booking_id, $admin_id, $reason)
    {
        $query = "UPDATE bookings SET status = 'ditolak', rejection_reason = :reason, handled_by = :admin, updated_at = NOW() WHERE booking_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([":reason" => $reason, ":admin" => $admin_id, ":id" => $booking_id]);

        return ['success' => true, 'message' => "Booking berhasil ditolak."];
    }
}
?>