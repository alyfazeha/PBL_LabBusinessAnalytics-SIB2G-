<?php
class Database {
    // 1. Tambah static property untuk menyimpan instance tunggal
    private static $instance = null;

    private $host = "localhost";
    private $port = "5432";
    private $db_name = "pbl";
    private $username = "postgres";
    private $password = "lovie180906";
    
    private $conn; 

    // 2. Jadikan constructor private agar objek tidak bisa dibuat dari luar
    private function __construct() {
        // Panggil koneksi di dalam constructor agar hanya dieksekusi sekali
        $this->connect();
    }
    
    // Metode baru untuk koneksi (dipanggil sekali di constructor)
    private function connect() {
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Database connection failed",
                "error" => $e->getMessage()
            ]);
            exit;
        }
    }
    
    // 3. Tambah metode Singleton: getInstance()
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database(); // Membuat instance baru (memanggil connect)
        }
        // Mengembalikan koneksi PDO yang sudah disimpan di properti $conn
        return self::$instance->conn;
    }
}
?>