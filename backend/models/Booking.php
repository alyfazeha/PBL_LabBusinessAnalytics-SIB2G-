<?php
require_once __DIR__ . "/../config/database.php";

class Booking
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /* ======================================================
       CEK KONFLIK DENGAN blocked_dates
       Jika sarana diblok pada tanggal & jam → booking ditolak
    ====================================================== */
    public function isBlocked($sarana_id, $tanggal, $start_time, $end_time)
    {
        $sql = "
            SELECT 1
            FROM blocked_dates
            WHERE sarana_id = :sarana_id
              AND :tanggal BETWEEN start_date AND end_date
              AND (
                    end_time > :start_time
                AND start_time < :end_time
              )
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':sarana_id' => $sarana_id,
            ':tanggal'   => $tanggal,
            ':start_time' => $start_time,
            ':end_time'   => $end_time
        ]);

        return $stmt->fetchColumn() ? true : false;
    }

    /* ======================================================
       CEK KONFLIK SESAMA BOOKING
       Bentrok jika: tanggal sama, sarana sama, jam overlap
    ====================================================== */
    public function hasConflict($sarana_id, $tanggal, $start_time, $end_time)
    {
        $sql = "
            SELECT 1
            FROM bookings
            WHERE sarana_id = :sarana_id
              AND tanggal = :tanggal
              AND status IN ('diajukan','disetujui')
              AND (
                    end_time > :start_time
                AND start_time < :end_time
              )
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':sarana_id' => $sarana_id,
            ':tanggal'   => $tanggal,
            ':start_time' => $start_time,
            ':end_time'   => $end_time
        ]);

        return $stmt->fetchColumn() ? true : false;
    }

    /* ======================================================
       CREATE BOOKING BARU
    ====================================================== */
    public function create($data)
    {
        $sql = "
            INSERT INTO bookings (
                mahasiswa_nim, booking_dosen_nidn,
                sarana_id, tanggal,
                sks, hours_per_sks, duration_hours,
                start_time, end_time,
                keperluan,
                created_by
            )
            VALUES (
                :nim, :nidn,
                :sarana_id, :tanggal,
                :sks, :hours_per_sks, :duration_hours,
                :start_time, :end_time,
                :keperluan,
                :created_by
            )
            RETURNING booking_id
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            ':nim' => $data['nim'],
            ':nidn' => $data['nidn'],
            ':sarana_id' => $data['sarana_id'],
            ':tanggal' => $data['tanggal'],
            ':sks' => $data['sks'],
            ':hours_per_sks' => 2, // CONSTANT
            ':duration_hours' => $data['sks'] * 2,
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':keperluan' => $data['keperluan'],
            ':created_by' => $data['created_by']
        ]);

        return $stmt->fetchColumn();
    }
}
?>