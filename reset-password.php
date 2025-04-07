<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = trim($_POST['identifier']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // Validate fields
    if (empty($identifier) || empty($newPassword) || empty($confirmPassword)) {
        echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
        exit();
    }

    if ($newPassword !== $confirmPassword) {
        echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
        exit();
    }

    // Check if the user exists
    $sql = "SELECT * FROM users WHERE email = :identifier OR contact_number = :identifier";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':identifier', $identifier);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Check if user already has a record in user_credentials
        $credentialSql = "SELECT * FROM user_credentials WHERE user_id = :user_id";
        $credentialStmt = $pdo->prepare($credentialSql);
        $credentialStmt->bindParam(':user_id', $user['id']);
        $credentialStmt->execute();
        $credentials = $credentialStmt->fetch(PDO::FETCH_ASSOC);

        if ($credentials) {
            // Update the existing password
            $updateSql = "UPDATE user_credentials SET password = :password WHERE user_id = :user_id";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->bindParam(':password', $hashedPassword);
            $updateStmt->bindParam(':user_id', $user['id']);

            if ($updateStmt->execute()) {
                echo "<script>alert('Password has been reset successfully. You can now log in.'); window.location.href='signin.html';</script>";
            } else {
                echo "<script>alert('Error updating password. Please try again.'); window.history.back();</script>";
            }
        } else {
            // Insert new credentials if none exist
            $insertSql = "INSERT INTO user_credentials (user_id, identifier, password) VALUES (:user_id, :identifier, :password)";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->bindParam(':user_id', $user['id']);
            $insertStmt->bindParam(':identifier', $identifier);
            $insertStmt->bindParam(':password', $hashedPassword);

            if ($insertStmt->execute()) {
                echo "<script>alert('Password has been reset successfully. You can now log in.'); window.location.href='signin.html';</script>";
            } else {
                echo "<script>alert('Error resetting password. Please try again.'); window.history.back();</script>";
            }
        }
    } else {
        echo "<script>alert('No user found with this email or phone number.'); window.history.back();</script>";
    }
}
?>
