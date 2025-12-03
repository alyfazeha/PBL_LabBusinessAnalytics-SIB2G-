<?php
class Database {
    private $host = "localhost";
    private $port = "5432";
    private $db_name = "pbl";
    private $username = "postgres";
    private $password = "lovie180906";
    
    // Properti $conn dibuat private karena hanya digunakan di dalam kelas
    private $conn; 

    public function getConnection() {        
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            $this->conn = new PDO($dsn, $this->username, $this->password);

            // Mode error PDO
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Mengembalikan dalam bentuk associative array
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Database connection failed",
                "error" => $e->getMessage()
            ]);
            exit; // Tetap menggunakan exit karena ini adalah error fatal
        }
        return $this->conn;
    }
}