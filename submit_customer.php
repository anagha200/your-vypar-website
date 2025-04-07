<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=u329714540_yourvypar', 'u329714540_yourvypar', 'YourVypar@123');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Database connection successful");
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieve employee_id from the session
    if (!isset($_SESSION['employee_id'])) {
        echo json_encode(['success' => false, 'error' => 'Employee ID not found in session']);
        exit();
    }

    $employeeId = $_SESSION['employee_id']; // Retrieve employee_id from session
    $leadType = $_POST['leadType'] ?? ''; // Retrieve lead type from dropdown
    $salesType = $_POST['salesType'] ?? ''; // Retrieve sales type from dropdown
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $remarks = $_POST['remarks'] ?? '';
    $selfieData = $_POST['selfieData'] ?? '';
    $mapLink = $_POST['mapLink'] ?? '';

    // Validate dropdown selections
    if (empty($leadType) || empty($salesType)) {
        echo json_encode(['success' => false, 'error' => 'Lead type and sales type are required.']);
        exit();
    }

    if (!empty($selfieData)) {
        // Save the selfie image
        if (preg_match('/^data:image\/(\w+);base64,/', $selfieData, $type)) {
            $data = substr($selfieData, strpos($selfieData, ',') + 1);
            $data = base64_decode($data);
            if ($data === false) {
                die(json_encode(['success' => false, 'error' => 'Base64 decode failed']));
            }

            // Define a path to save the selfie image
            $selfiePath = 'uploads/' . uniqid('selfie_', true) . '.png';
            file_put_contents($selfiePath, $data);
        } else {
            die(json_encode(['success' => false, 'error' => 'Invalid image data']));
        }
    } else {
        die(json_encode(['success' => false, 'error' => 'Selfie data not submitted.']));
    }

    // Prepare and execute the SQL statement
    try {
        $stmt = $pdo->prepare("INSERT INTO customers (employee_id, lead_type, sales_type, name, email, phone, address, map_link, remarks, selfie_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$employeeId, $leadType, $salesType, $name, $email, $phone, $address, $mapLink, $remarks, $selfiePath]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
