<?php
error_reporting(E_ALL); // Enable error reporting
ini_set('display_errors', 1); // Display errors for debugging

// Define constants
define('FILE_PATH', __DIR__ . '/uploads/CustomerDetails.csv'); // Path to the CSV file
date_default_timezone_set('Asia/Kolkata'); // Set your timezone

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $employeeId = $_POST['employee_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $loanType = $_POST['loanType'] ?? '';
    $address = $_POST['address'] ?? '';
    $remarks = $_POST['remarks'] ?? '';
    $submissionDate = date('Y-m-d');

    // Create uploads directory if it doesn't exist
    if (!is_dir(__DIR__ . '/uploads')) {
        mkdir(__DIR__ . '/uploads', 0755, true);
    }

    // Check if the file exists
    $isNewFile = !file_exists(FILE_PATH);
    
    // Open the file in append mode
    $file = fopen(FILE_PATH, 'a');
    
    // If the file is new, write the header row
    if ($isNewFile) {
        fputcsv($file, ['Employee ID', 'Name', 'Email', 'Phone', 'Loan Type', 'Address', 'Remarks', 'Submission Date']);
    }
    
    // Write the form data as a new row
    fputcsv($file, [$employeeId, $name, $email, $phone, $loanType, $address, $remarks, $submissionDate]);
    
    // Close the file
    fclose($file);

    echo 'Form submitted successfully! Data has been saved in the CSV file.';
} else {
    echo 'Invalid request method.';
}
