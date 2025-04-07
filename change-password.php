<?php
session_start();
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $input = json_decode(file_get_contents('php://input'), true);

    // Retrieve employee ID from the request
    $empId = $input['empId'] ?? null;
    $newPassword = $input['newPassword'] ?? null;

    // Check if the newPassword is set and empId is present
    if ($newPassword && $empId) {
        // Validate password (example: at least 8 characters)
        if (strlen($newPassword) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
            exit;
        }

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Database connection (replace with your actual database credentials)
        $host = 'localhost';
        $db = 'u329714540_yourvypar'; 
        $user = 'u329714540_yourvypar'; 
        $pass = 'YourVypar@123'; 

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Prepare the SQL statement to update the password
            $stmt = $pdo->prepare("UPDATE employee_details1 SET password = :password WHERE employee_id = :empId");
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':empId', $empId);

            // Execute the statement
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Password changed successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to change password.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'New password or Employee ID is missing.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
