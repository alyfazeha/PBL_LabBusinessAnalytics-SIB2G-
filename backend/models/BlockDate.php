<?php
// Tidak perlu require database.php, karena koneksi PDO di-inject via constructor
class BlockedDate
{
    private $conn;
    private $table = "blocked_dates";

    // Menerima objek PDO dari luar
    public function __construct($db)
    {
        $this->conn = $db;
    }

    /* ======================================================
       CREATE BLOCKED DATE
    ====================================================== */
    public function create($sarana_id, $start_date, $end_date, $start_time, $end_time, $reason, $created_by)
    {
        $query = "
            INSERT INTO {$this->table} 
            (sarana_id, start_date, end_date, start_time, end_time, reason, created_by)
            VALUES (:sarana_id, :start_date, :end_date, :start_time, :end_time, :reason, :created_by)
        ";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':sarana_id' => $sarana_id,
            ':start_date' => $start_date,
            ':end_date' => $end_date,
            ':start_time' => $start_time,
            ':end_time' => $end_time,
            ':reason' => $reason,
            ':created_by' => $created_by
        ]);
    }

    /* ======================================================
       LIST SEMUA BLOCKED DATES
    ====================================================== */
    public function getAll()
    {
        $query = "
            SELECT bd.*, s.nama_sarana, u.username AS created_by_user
            FROM {$this->table} bd
            LEFT JOIN sarana s ON bd.sarana_id = s.sarana_id
            LEFT JOIN users u ON bd.created_by = u.user_id
            ORDER BY bd.start_date DESC
        ";
        
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // ... getById dan delete (dihapus/diasumsikan sudah benar jika menggunakan PDO) ...

    /* ======================================================
       CEK APAKAH RENTANG DIBLOK
    ====================================================== */
    public function isBlockedRange($sarana_id, $tanggal, $start, $end)
    {
        $query = "
            SELECT 1 FROM {$this->table}
            WHERE sarana_id = :sarana_id
              AND :tanggal BETWEEN start_date AND end_date
              AND (
                    end_time > :start
                AND start_time < :end
              )
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->execute([
            ':sarana_id' => $sarana_id,
            ':tanggal'   => $tanggal,
            ':start'     => $start,
            ':end'       => $end
        ]);

        return $stmt->fetchColumn() ? true : false;
    }
}
?>