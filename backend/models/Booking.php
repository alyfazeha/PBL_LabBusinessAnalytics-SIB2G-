<?php
require_once __DIR__ . "/../config/database.php";

class Booking
{
    private $conn;

    public function __construct()
    {
        // Mengambil koneksi PDO dari Singleton
        $this->conn = Database::getInstance();
    }

    /* ======================================================
       CEK KONFLIK DENGAN blocked_dates
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
    ====================================================== */
    public function hasConflict($sarana_id, $tanggal, $start_time, $end_time)
    {
        $sql = "
            SELECT 1
            FROM bookings
            WHERE sarana_id = :sarana_id
              AND tanggal = :tanggal
              AND status = 'disetujui' -- HANYA CEK DENGAN BOOKING YANG SUDAH DISETUJUI
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
        // Perhitungan SKS & Durasi jam harus ada
        $hours_per_sks = 2; // Asumsi konstan
        $duration_hours = $data['sks'] * $hours_per_sks;

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

        $params = [
            ':nim' => $data['nim'],
            ':nidn' => $data['nidn'],
            ':sarana_id' => $data['sarana_id'],
            ':tanggal' => $data['tanggal'],
            ':sks' => $data['sks'],
            ':hours_per_sks' => $hours_per_sks,
            ':duration_hours' => $duration_hours,
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':keperluan' => $data['keperluan'],
            ':created_by' => $data['created_by']
        ];

        if ($stmt->execute($params)) {
            // Menggunakan fetchColumn untuk mengambil hasil dari RETURNING booking_id
            return $stmt->fetchColumn();
        }
        return false;
    }
}
?>