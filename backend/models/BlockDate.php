<?php

class BlockedDate
{
    private $conn;
    private $table = "blocked_dates";

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
        $query = "SELECT * FROM {$this->table} ORDER BY start_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /* ======================================================
       GET BY ID
    ====================================================== */
    public function getById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE block_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ======================================================
       DELETE BLOCKED DATE
    ====================================================== */
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE block_id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

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
            ':tanggal' => $tanggal,
            ':start' => $start,
            ':end' => $end
        ]);

        return $stmt->fetchColumn() ? true : false;
    }
}
?>