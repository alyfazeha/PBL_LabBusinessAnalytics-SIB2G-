<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../config/database.php";

try {
    $db = Database::getInstance();

    $startDate = $_GET['start_date'] ?? date('Y-m-d');
    $endDate   = $_GET['end_date'] ?? date('Y-m-d', strtotime('+6 days'));

    // Ambil Booking (Status: Diajukan & Disetujui)
    $sqlBooking = "
        SELECT 
            b.booking_id,
            b.tanggal, 
            b.start_time, 
            b.end_time, 
            b.status
        FROM bookings b
        WHERE b.tanggal BETWEEN :start AND :end
        AND b.status IN ('diajukan', 'disetujui')
        ORDER BY b.tanggal ASC, b.start_time ASC
    ";

    $stmt = $db->prepare($sqlBooking);
    $stmt->execute([':start' => $startDate, ':end' => $endDate]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Grouping Data
    $schedule = [];
    foreach ($bookings as $row) {
        $date = $row['tanggal'];
        if (!isset($schedule[$date])) $schedule[$date] = [];
        
        $schedule[$date][] = [
            'waktu'     => substr($row['start_time'], 0, 5) . ' - ' . substr($row['end_time'], 0, 5),
            'status'    => $row['status']
        ];
    }

    echo json_encode(['success' => true, 'data' => $schedule]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>