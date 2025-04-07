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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']); // Email or Phone number
    $password = trim($_POST['password']); // Password

    if (empty($identifier) || empty($password)) {
        echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
        exit();
    }

    // Fetch user details from the database
    $sql = "SELECT * FROM users WHERE email = :identifier OR contact_number = :identifier";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':identifier', $identifier);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Check if the user has a password stored in user_credentials
        $credentialSql = "SELECT * FROM user_credentials WHERE user_id = :user_id";
        $credentialStmt = $pdo->prepare($credentialSql);
        $credentialStmt->bindParam(':user_id', $user['id']);
        $credentialStmt->execute();
        $credentials = $credentialStmt->fetch(PDO::FETCH_ASSOC);

        if ($credentials) {
            // Verify the entered password against the hashed password
            if (password_verify($password, $credentials['password'])) {
                // Store session data
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_id'] = $user['id']; 

                // Redirect to the welcome page
                header('Location: welcome.php');
                exit();
            } else {
                echo "<script>alert('Invalid password.'); window.history.back();</script>";
            }
        } else {
            // If no password is stored, force reset
            echo "<script>alert('No password found. Please reset your password.'); window.location.href='reset-password.html';</script>";
        }
    } else {
        // No user found
        echo "<script>alert('No registered user found with this email or phone number.'); window.history.back();</script>";
    }
}
?>
