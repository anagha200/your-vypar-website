<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? null;
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $softwareType = $_POST['softwareType'] ?? null;
    $address = $_POST['address'] ?? null;
    $remarks = $_POST['remarks'] ?? null;

    if (!$name || !$email || !$phone || !$softwareType) {
        echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
        exit;
    }

    $conn = new mysqli('localhost', 'u329714540_yourvypar', 'YourVypar@123', 'u329714540_yourvypar');

    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
        exit;
    }

    $stmt = $conn->prepare('INSERT INTO software_details (name, email, phone, softwareType, address, remarks) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssss', $name, $email, $phone, $softwareType, $address, $remarks);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data stored successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error storing data: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
