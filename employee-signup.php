<?php
// employee-signup.php

// Include Composer's autoload
require 'vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include the email sender function
include 'email_sender.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database credentials
$dbHost = "localhost";
$dbUsername = "u329714540_yourvypar"; // Change to your database username
$dbPassword = "YourVypar@123"; // Change to your database password
$dbName = "u329714540_yourvypar"; // Change to your database name

// Connect to your database
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data from POST request
    $employeeName = $_POST['employeeName'];
    $email = $_POST['email']; // Ensure this is unique in your table
    $contactNumber = $_POST['contactNumber'];
    $designation = $_POST['designation'];

    // Get the last employee ID for unique ID generation
    $result = $conn->query("SELECT employee_id FROM employee_details1 ORDER BY employee_id DESC LIMIT 1");
    $lastEmployeeId = $result->fetch_row()[0] ?? null;

    $prefix = "EMP2024";
    if ($lastEmployeeId) {
        // Extract the numeric part after "ADR2023"
        $lastNumber = (int) substr($lastEmployeeId, strlen($prefix));
        // Increment by 1
        $nextNumber = $lastNumber + 1;
    } else {
        // If no previous ID exists, start from 1
        $nextNumber = 1;
    }

    // Generate a unique employee_id
    $employeeId = $prefix . str_pad($nextNumber, 2, '0', STR_PAD_LEFT); // Generate a unique ID

    // Generate a random password
    $password = bin2hex(random_bytes(4)); // Generate a random 8-character password

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO employee_details1 (employee_id, name, email, contact_number, designation, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $employeeId, $employeeName, $email, $contactNumber, $designation, $hashedPassword);

    // Response array
    $response = [];

    // Execute the statement and check if it was successful
    if ($stmt->execute()) {
        // Send the welcome email
        $emailResult = sendWelcomeEmail($email, $employeeName, $employeeId, $password);

        // Check if the email was sent successfully
        if ($emailResult) {
            $response = [
                'success' => true,
                'message' => 'Employee added successfully!',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Employee added, but failed to send email.',
            ];
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'Failed to add employee. Please try again.',
        ];
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Return response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
