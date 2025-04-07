<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

session_start(); // Start the session at the beginning of the script

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
if (!isset($input['employee_id']) || empty(trim($input['employee_id'])) ||
    !isset($input['password']) || empty(trim($input['password']))) {
    http_response_code(400);  // Bad Request
    echo json_encode(['error' => 'Invalid or missing Employee ID or Password']);
    exit;
}

$employeeId = trim($input['employee_id']);
$password = trim($input['password']);

// Optional: Validate Employee ID format (adjust regex as needed)
if (!preg_match("/^[A-Za-z0-9\-]+$/", $employeeId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid Employee ID format']);
    exit;
}

try {
    // Query to get the employee details using `employee_id` field
    $stmt = $pdo->prepare('SELECT employee_id, password FROM employee_details1 WHERE employee_id = ?');
    $stmt->execute([$employeeId]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employee) {
        // Check if the stored password is hashed or plain text
        if (strlen($employee['password']) < 60) { // Assuming hashed passwords are always longer
            // Compare plain text password
            if ($password === $employee['password']) {
                // Login successful, store employee_id in the session
                $_SESSION['employee_id'] = $employee['employee_id']; // Store the employee ID in the session
                http_response_code(200);
                echo json_encode(['success' => 'Login successful']);
            } else {
                http_response_code(401); 
                echo json_encode(['error' => 'Incorrect Employee ID or Password']);
            }
        } else {
            // Verify hashed password
            if (password_verify($password, $employee['password'])) {
                // Login successful, store employee_id in the session
                $_SESSION['employee_id'] = $employee['employee_id']; // Store the employee ID in the session
                http_response_code(200);
                echo json_encode(['success' => 'Login successful']);
            } else {
                http_response_code(401); 
                echo json_encode(['error' => 'Incorrect Employee ID or Password']);
            }
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Employee not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>