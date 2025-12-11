<?php
// Hapus header content-type di sini karena ini class library, bukan output JSON langsung
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Booking.php";
require_once __DIR__ . "/../../models/BlockedDate.php";
require_once __DIR__ . "/../../config/auth.php";


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

    /* ======================================================
        LOGIKA PERHITUNGAN WAKTU BOOKING (Pindah ke Controller)
    ====================================================== */
    private function calculateBookingTime($sks, $start_time, $tanggal)
    {
        // Aturan: 1 SKS = 2 jam akademik. 1 jam akademik = 50 menit.
        // Total durasi dalam menit: SKS * 2 * 50 = SKS * 100 menit
        $minutes_per_sks_unit = 100; 
        $hours_per_sks_db = 2; // Nilai tetap untuk disimpan ke DB sebagai referensi jam akademik

        $duration_minutes = $sks * $minutes_per_sks_unit;
        
        // Hitung End Time
        try {
            // Gabungkan tanggal dan waktu mulai
            $start_dt = new DateTime($tanggal . ' ' . $start_time);
            $end_dt = clone $start_dt;
            
            // Tambahkan durasi total dalam menit
            $end_dt->modify('+' . $duration_minutes . ' minutes'); 

            $end_time = $end_dt->format('H:i:s');
            
        } catch (Exception $e) {
            return ['error' => 'Kesalahan format waktu/tanggal saat perhitungan: ' . $e->getMessage()];
        }

        return [
            'hours_per_sks' => $hours_per_sks_db,
            'duration_hours' => $duration_minutes / 60,
            'end_time' => $end_time // end_time hasil perhitungan (HH:ii:ss)
        ];
    }

    public function createBooking($data)
    {        
        $nim       = $data['nim'];
        $nidn      = $data['nidn'];
        $sarana_id = $data['sarana_id'];
        $tanggal   = $data['tanggal'];
        $start     = $data['start_time'];
        $keperluan = $data['keperluan'];
        $created_by= $data['created_by'];
        
        // 1. Hitung END TIME di Controller
        $time_data = $this->calculateBookingTime(
            $data['sks'], 
            $data['start_time'], 
            $data['tanggal']
        );
        
        // Cek jika ada error dari perhitungan waktu
        if (isset($time_data['error'])) {
            return ['success' => false, 'message' => $time_data['error']];
        }

        // Ambil hasil perhitungan
        $end = $time_data['end_time']; 

        // 2. SISIPKAN DATA WAKTU HASIL PERHITUNGAN KE ARRAY $data
        $data['end_time']         = $end; 
        $data['hours_per_sks']    = $time_data['hours_per_sks'];
        $data['duration_hours']   = $time_data['duration_hours'];

        // 3. Cek apakah tanggal diblokir admin
        // Gunakan metode yang tersedia di Model BlockedDate. Asumsi isBlockedRange/isBlocked adalah metode yang benar.
        $is_blocked = false;
        if (method_exists($this->blocked, 'isBlockedRange')) {
            $is_blocked = $this->blocked->isBlockedRange($sarana_id, $tanggal, $start, $end);
        } else {
            $is_blocked = $this->booking->isBlocked($sarana_id, $tanggal, $start, $end);
        }

        if ($is_blocked) {
            return ['success' => false, 'message' => 'Waktu ini diblokir oleh admin.'];
        }

        // 4. Cek bentrok jadwal
        if ($this->booking->hasConflict($sarana_id, $tanggal, $start, $end)) {
            return ['success' => false, 'message' => 'Jadwal bentrok dengan booking lain.'];
        }

        // 5. Simpan data (Model menerima data yang sudah lengkap)
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

    /* =====================================================
    Tambahkan Helper untuk Ambil Detail Booking
    ====================================================== */
    private function getBookingDetails($booking_id)
    {
        // Mengambil data penting: sarana_id, waktu, dan status saat ini.
        $query = "SELECT sarana_id, tanggal, start_time, end_time, status FROM bookings WHERE booking_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $booking_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =====================================================
    REVISI UTAMA: Logika Approval
    ====================================================== */
    public function approveBooking($booking_id, $admin_id)
    {
        // 1. Ambil detail booking
        $booking_data = $this->getBookingDetails($booking_id); 
        
        if (!$booking_data) {
            return ['success' => false, 'message' => "Booking ID tidak ditemukan."];
        }
        
        // 2. Cek status harus diajukan
        if ($booking_data['status'] !== 'diajukan') {
            return ['success' => false, 'message' => "Gagal menyetujui. Booking sudah diproses ({$booking_data['status']})."];
        }

        $sarana_id = $booking_data['sarana_id'];
        $tanggal   = $booking_data['tanggal'];
        $start     = $booking_data['start_time'];
        $end       = $booking_data['end_time'];

        // 3. RE-CHECK: Konflik dengan Blocked Date (dari model BlockedDate)
        if ($this->blocked->isBlockedRange($sarana_id, $tanggal, $start, $end)) {
            return ['success' => false, 'message' => 'Gagal menyetujui. Waktu ini telah diblokir oleh Admin setelah pengajuan.'];
        }

        // 4. RE-CHECK: Konflik dengan Booking LAIN yang SUDAH DISETUJUI
        // Cek bentrok dengan booking lain di sarana, tanggal, dan jam yang sama.
        $conflict_query = "
            SELECT 1 
            FROM bookings 
            WHERE sarana_id = :sarana_id
            AND tanggal = :tanggal
            AND status = 'disetujui' -- **HANYA CEK DENGAN YANG SUDAH DISETUJUI**
            AND booking_id != :current_id -- Kecualikan ID booking yang sedang diproses
            AND (
                    end_time > :start
                AND start_time < :end
            )
            LIMIT 1;
        ";
        
        $stmt_conflict = $this->conn->prepare($conflict_query);
        $stmt_conflict->execute([
            ':sarana_id'  => $sarana_id,
            ':tanggal'    => $tanggal,
            ':start'      => $start,
            ':end'        => $end,
            ':current_id' => $booking_id
        ]);

        if ($stmt_conflict->rowCount() > 0) {
            return ['success' => false, 'message' => 'Gagal menyetujui. Jadwal bentrok dengan booking lain yang sudah disetujui.'];
        }

        // 5. Lakukan UPDATE: Ubah status menjadi 'disetujui'
        // Perhatikan penambahan AND status = 'diajukan' di WHERE clause untuk atomicity.
        $query = "UPDATE bookings SET status = 'disetujui', handled_by = :admin, updated_at = NOW() WHERE booking_id = :id AND status = 'diajukan'";
        $stmt = $this->conn->prepare($query);
        $ok = $stmt->execute([":admin" => $admin_id, ":id" => $booking_id]);

        if ($ok && $stmt->rowCount() > 0) {
            return ['success' => true, 'message' => "Booking berhasil disetujui."];
        } else {
            return ['success' => false, 'message' => "Gagal menyetujui booking (ID tidak ditemukan atau sudah diproses)."];
        }
    }

    public function rejectBooking($booking_id, $admin_id, $reason){
        // Tambahkan kondisi AND status = 'diajukan'
        $query = "UPDATE bookings SET status = 'ditolak', rejection_reason = :reason, handled_by = :admin, updated_at = NOW() WHERE booking_id = :id AND status = 'diajukan'"; 
        $stmt = $this->conn->prepare($query);
        $ok = $stmt->execute([":reason" => $reason, ":admin" => $admin_id, ":id" => $booking_id]);

        if ($ok && $stmt->rowCount() > 0) { // Cek rowCount > 0
            return ['success' => true, 'message' => "Booking berhasil ditolak."];
        } else {
            return ['success' => false, 'message' => "Gagal menolak booking (ID tidak ditemukan atau sudah diproses)."];
        }
    }
}
?>