<?php
// checkout.php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");  
header("Access-Control-Allow-Headers: Content-Type");

// Database connection settings
define("DB_HOST", "localhost");
define("DB_USER", "u329714540_yourvypar");
define("DB_PASSWORD", "YourVypar@123");
define("DB_DATABASE", "u329714540_yourvypar");
define("DB_PORT", "3306");

// Create connection using PDO
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_DATABASE . ';port=' . DB_PORT,
        DB_USER,
        DB_PASSWORD
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Read input from request body
$input = json_decode(file_get_contents('php://input'), true);

// Validate the input parameters
if (
    !isset($input['employee_id']) || empty(trim($input['employee_id'])) ||
    !isset($input['check_out_time']) || empty(trim($input['check_out_time']))
) {
    http_response_code(400);  // Bad Request
    echo json_encode(['error' => 'Invalid or missing parameters']);
    exit;
}

$empId = trim($input['employee_id']);
$checkOutTime = trim($input['check_out_time']);

// Optional: Validate Employee ID format
if (!preg_match("/^[A-Za-z0-9\-]+$/", $empId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid Employee ID format']);
    exit;
}

try {
    // Find the latest attendance record for today
    $currentDate = date('Y-m-d'); // Assuming check-out is for today
    $stmt = $pdo->prepare('SELECT * FROM attendance WHERE employee_id = ? AND check_date = ? ORDER BY checkin_time DESC LIMIT 1');
    $stmt->execute([$empId, $currentDate]);
    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($attendance) {
        if ($attendance['checkout_time'] !== null) {
            // Already checked out
            http_response_code(400);  // Bad Request
            echo json_encode(['error' => 'Already checked out today.']);
        } else {
            // Update the check-out time
            $stmt = $pdo->prepare('UPDATE attendance SET checkout_time = ? WHERE employee_id = ? AND check_date = ?');
            $stmt->execute([$checkOutTime, $empId, $currentDate]);

            http_response_code(200);  // OK
            echo json_encode(['success' => 'Check-Out recorded successfully']);
        }
    } else {
        // No check-in record found
        http_response_code(400);  // Bad Request
        echo json_encode(['error' => 'No check-in record found for today.']);
    }
} catch (PDOException $e) {
    http_response_code(500);  // Internal Server Error
    echo json_encode(['error' => 'Failed to record Check-Out: ' . $e->getMessage()]);
}
?>
