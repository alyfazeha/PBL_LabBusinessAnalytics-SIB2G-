<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Booking.php";
require_once __DIR__ . "/../models/BlockedDate.php";

class BookingController
{
    private $conn;
    private $booking;
    private $blocked;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();

        $this->booking = new Booking();
        $this->blocked = new BlockedDate($this->conn);
    }

    /* ======================================================
       CREATE BOOKING
    ====================================================== */
    public function createBooking($data)
    {
        $sarana_id = $data['sarana_id'];
        $tanggal   = $data['tanggal'];
        $start     = $data['start_time'];
        $end       = $data['end_time'];

        // 1. Cek konflik dengan blocked_dates
        if ($this->blocked->isBlockedRange($sarana_id, $tanggal, $start, $end)) {
            return [
                'success' => false,
                'message' => 'Waktu ini diblok oleh admin.'
            ];
        }

        // 2. Cek konflik dengan booking lain
        if ($this->booking->hasConflict($sarana_id, $tanggal, $start, $end)) {
            return [
                'success' => false,
                'message' => 'Bentrok dengan booking lain.'
            ];
        }

        // 3. Simpan booking
        $booking_id = $this->booking->create($data);

        return [
            'success' => true,
            'message' => 'Booking berhasil dibuat.',
            'booking_id' => $booking_id
        ];
    }

    /* ======================================================
       APPROVE BOOKING
    ====================================================== */
    public function approveBooking($booking_id, $admin_id)
    {
        $query = "
            UPDATE bookings
            SET status = 'disetujui',
                handled_by = :admin,
                updated_at = NOW()
            WHERE booking_id = :id
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->execute([
            ":admin" => $admin_id,
            ":id" => $booking_id
        ]);

        return [
            'success' => true,
            'message' => "Booking berhasil disetujui."
        ];
    }

    /* ======================================================
       REJECT BOOKING
    ====================================================== */
    public function rejectBooking($booking_id, $admin_id, $reason)
    {
        $query = "
            UPDATE bookings
            SET status = 'ditolak',
                rejection_reason = :reason,
                handled_by = :admin,
                updated_at = NOW()
            WHERE booking_id = :id
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->execute([
            ":reason" => $reason,
            ":admin" => $admin_id,
            ":id" => $booking_id
        ]);

        return [
            'success' => true,
            'message' => "Booking berhasil ditolak."
        ];
    }
}