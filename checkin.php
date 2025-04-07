<?php
// checkin.php

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
    !isset($input['check_in_time']) || empty(trim($input['check_in_time'])) ||
    !isset($input['check_date']) || empty(trim($input['check_date']))
) {
    http_response_code(400);  // Bad Request
    echo json_encode(['error' => 'Invalid or missing parameters']);
    exit;
}

$empId = trim($input['employee_id']);
$checkInTime = trim($input['check_in_time']);
$checkDate = trim($input['check_date']);

// Optional: Validate Employee ID format
if (!preg_match("/^[A-Za-z0-9\-]+$/", $empId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid Employee ID format']);
    exit;
}

try {
    // Check if the employee has already checked in today
    $stmt = $pdo->prepare('SELECT * FROM attendance WHERE employee_id = ? AND check_date = ?');
    $stmt->execute([$empId, $checkDate]);
    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($attendance) {
        // Employee has already checked in today
        http_response_code(400);  // Bad Request
        echo json_encode(['error' => 'Already checked in today.']);
    } else {
        // Insert the check-in record
        $stmt = $pdo->prepare('INSERT INTO attendance (employee_id, check_date, checkin_time) VALUES (?, ?, ?)');
        $stmt->execute([$empId, $checkDate, $checkInTime]);

        http_response_code(200);  // OK
        echo json_encode(['success' => 'Check-In recorded successfully']);
    }
} catch (PDOException $e) {
    http_response_code(500);  // Internal Server Error
    echo json_encode(['error' => 'Failed to record Check-In: ' . $e->getMessage()]);
}
?>
