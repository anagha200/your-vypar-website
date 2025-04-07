<?php
// Database connection (update these values with your own database credentials)
$host = 'localhost';
$dbname = 'test';
$username = 'root';
$password = '';

// Create a PDO instance for connecting to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $companyName = $_POST['company_name'];
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $contactNumber = $_POST['contact_number'];
    $address = $_POST['address'];
    $message = $_POST['message'];

    // Insert the form data into the database
    $sql = "INSERT INTO users (company_name, full_name, email, contact_number, address, message) 
            VALUES (:company_name, :full_name, :email, :contact_number, :address, :message)";
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':company_name', $companyName);
    $stmt->bindParam(':full_name', $fullName);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':contact_number', $contactNumber);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':message', $message);

    try {
        // Execute the statement
        $stmt->execute();
        
        // After successful registration, get the user id
        $userId = $pdo->lastInsertId();
        
        // You may want to set the trial start date here, if it's different
        $trialStartDate = date("Y-m-d H:i:s");

        // Update the trial start date (in case you want to set it manually)
        $updateTrialSql = "UPDATE users SET trial_start_date = :trial_start_date WHERE id = :id";
        $updateStmt = $pdo->prepare($updateTrialSql);
        $updateStmt->bindParam(':trial_start_date', $trialStartDate);
        $updateStmt->bindParam(':id', $userId);
        $updateStmt->execute();

        // Redirect to the employee-signup.html page after successful registration
        header('Location: signin.html');
        exit();

    } catch (PDOException $e) {
        // Handle error (e.g., if the email is already in use)
        echo "Error: " . $e->getMessage();
    }
}
?>
