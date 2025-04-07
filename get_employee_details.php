<?php
// get_employee_details.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection settings
$host = 'localhost';
$db_name = 'u329714540_yourvypar'; // Replace with your database name
$username = 'u329714540_yourvypar'; // Replace with your database username
$password = 'YourVypar@123'; // Replace with your database password

// Create a connection
$conn = new mysqli($host, $username, $password, $db_name);

// Check the connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Read the POST data
$data = json_decode(file_get_contents("php://input"), true);
$employee_id = $data['employee_id'] ?? null;

if ($employee_id) {
    // Validate the employee_id to prevent SQL injection
    if (!preg_match('/^[a-zA-Z0-9]+$/', $employee_id)) {
        echo json_encode(['success' => false, 'message' => 'Invalid employee ID format.']);
        exit();
    }

    // Prepare and execute the query to fetch employee details along with image path
    $query = "SELECT employee_id, name, designation, contact_number FROM employee_details1 WHERE employee_id = ?";  // Added WHERE clause

    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Query preparation failed.']);
        exit();
    }
    
    $stmt->bind_param("s", $employee_id); // Use 's' for string
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $employee_details = $result->fetch_assoc();
        echo json_encode(['success' => true, 'details' => $employee_details]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Employee not found.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid employee ID.']);
}

$conn->close();
?>
