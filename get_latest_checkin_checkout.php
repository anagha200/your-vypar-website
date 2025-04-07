<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");  
header("Access-Control-Allow-Headers: Content-Type");

session_start(); // Start the session

// Database connection settings
define("DB_HOST", "localhost");
define("DB_USER", "u329714540_yourvypar");
define("DB_PASSWORD", "YourVypar@123");
define("DB_DATABASE", "u329714540_yourvypar");
define("DB_PORT", "3306");

// Create connection using PDO
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_DATABASE . ';port=' . DB_PORT, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Read input from request body
$input = json_decode(file_get_contents('php://input'), true);

// Validate the employee_id parameter from session
if (!isset($_SESSION['employee_id'])) {
    http_response_code(400);  // Bad Request
    echo json_encode(['message' => 'User not authenticated']);
    exit;
}

$empId = $_SESSION['employee_id']; // Use session ID

try {
    // Query to get the latest check-in and check-out times for the specified employee
    $stmt = $pdo->prepare('SELECT check_date, checkin_time, checkout_time 
                            FROM attendance 
                            WHERE employee_id = ? 
                            ORDER BY check_date DESC, checkin_time DESC 
                            LIMIT 1');
    $stmt->execute([$empId]);
    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($attendance) {
        http_response_code(200);  // OK
        echo json_encode(['latest_attendance' => $attendance]);
    } else {
        http_response_code(404);  // No records found
        echo json_encode(['message' => 'No attendance records found for the specified Employee ID']);
    }
} catch (PDOException $e) {
    http_response_code(500);  // Internal Server Error
    echo json_encode(['message' => 'Failed to retrieve attendance: ' . $e->getMessage()]);
}
?>
