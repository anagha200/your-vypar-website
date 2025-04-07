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

// Check if the user is logged in
if (!isset($_SESSION['full_name'])) {
    header('Location: sign-in.html');
    exit();
}

// Fetch user details from the database
$userId = $_SESSION['user_id']; // Assuming user ID is stored in session after successful login
$sql = "SELECT * FROM users WHERE id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // If user is not found in the database, redirect to sign-in
    header('Location: sign-in.html');
    exit();
}

$trialStartDate = $user['trial_start_date'];
$employeeCount = $user['employee_count'];
$trialEndDate = date('Y-m-d H:i:s', strtotime($trialStartDate . ' + 7 days'));
$daysLeft = max(0, (strtotime($trialEndDate) - time()) / 86400); // Days remaining in the trial

// Start Free Trial Button Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_trial'])) {
    // Set the trial start date if not already set
    if (!$trialStartDate) {
        $trialStartDate = date("Y-m-d H:i:s");
        $updateSql = "UPDATE users SET trial_start_date = :trial_start_date WHERE id = :user_id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->bindParam(':trial_start_date', $trialStartDate);
        $updateStmt->bindParam(':user_id', $userId);
        $updateStmt->execute();
    }
}

// Calculate the price based on the number of employees
$pricePerEmployee = 100; // 100 Rs per employee
$totalPrice = $employeeCount * $pricePerEmployee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', Arial, sans-serif;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 0 20px;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #0d47a1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-bottom: 3px solid #ffffff;
        }

        header img {
            height: 100px;
            object-fit: contain;
            transition: all 0.3s ease;
        }

        nav ul {
            display: flex;
            list-style: none;
            padding: 0;
        }

        nav ul li {
            margin: 0 15px;
        }

        nav ul li a {
            text-decoration: none;
            color: white;
            font-weight: 500;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        nav ul li a:hover {
            color: #81c784;
        }

        .container {
            max-width: 800px;
            width: 100%;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            text-align: center;
            margin-top: 100px;
        }

        h1 {
            font-size: 2.5rem;
            color: #0d47a1;
            margin-bottom: 20px;
        }

        .message {
            font-size: 1.2rem;
            color: #1565c0;
            margin-top: 20px;
        }

        .trial-warning {
            color: #f44336;
            font-size: 1.2rem;
            margin-top: 20px;
        }

        .button {
            padding: 12px 20px;
            background-color: #0d47a1;
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin: 10px;
            width: 100%;
            max-width: 200px;
            margin-top: 20px;
        }

        .button:hover {
            background-color: #1976d2;
            transform: translateY(-2px);
        }

        .disabled {
            background-color: #b0bec5;
            cursor: not-allowed;
        }

        /* Centering button container */
        .button-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            flex-direction: column;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px;
            }

            header img {
                height: 60px;
                margin-bottom: 10px;
            }

            nav ul {
                display: none;
                flex-direction: column;
                width: 100%;
                padding-top: 10px;
            }

            nav ul.show {
                display: flex;
            }

            nav ul li {
                margin: 10px 0;
                text-align: center;
            }

            .button {
                width: 100%;
                max-width: none;
            }

            .container {
                padding: 15px;
            }
        }

        /* Footer Styles */
        footer {
            background: linear-gradient(135deg, #392C6E, #311B66, #11011B);
            padding: 20px;
            text-align: center;
            color: white;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        footer p {
            font-size: 14px;
        }

        footer a {
            color: white;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <header>
        <img src="img/Your.png" alt="YourVypar Logo">
        <nav>
            <ul>
                <li><a href="testing.html">Home</a></li>
                <li><a href="aboutus.html">About Us</a></li>
                <li><a href="services.html">Services</a></li>
                <li><a href="contactus.html">Contact Us</a></li>
                <li><a href="careers.html">Careers</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>

        <!-- Display trial expiration warning -->
        <?php if ($trialStartDate && $daysLeft > 0): ?>
            <div class="message">
                Your free trial is active. <?php echo floor($daysLeft); ?> day(s) left.
            </div>

            <!-- Buttons to "Pay Now" or "Start Free Trial" -->
            <div class="button-container">
                <a href="payment-description.html">
                    <button class="button">Pay Now</button>
                </a>
                <a href="employee-signup.html">
                    <button class="button">Start a Free Trial</button>
                </a>
            </div>

        <?php elseif ($trialStartDate && $daysLeft <= 0): ?>
            <div class="message">
                Your free trial has expired. Please proceed with payment.
            </div>
            <div>
                <h3>Price: <?php echo $totalPrice; ?> Rs for <?php echo $employeeCount; ?> employee(s)</h3>
                <a href="payment.html">
                    <button class="button">Pay Now</button>
                </a>
            </div>
        <?php else: ?>
            <div class="button-container">
                <a href="employee-signup.html">
                    <button class="button">Start Free Trial</button>
                </a>
            </div>
        <?php endif; ?>

        <!-- If trial is ending soon, show a warning -->
        <?php if ($daysLeft <= 3 && $daysLeft > 0): ?>
            <div class="trial-warning">
                Hurry! Your trial is ending in <?php echo floor($daysLeft); ?> day(s).
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2024 YourVypar. All rights reserved. | <a href="#">Privacy Policy</a></p>
    </footer>

    <script>
        // Mobile Navigation toggle
        const nav = document.querySelector('nav ul');
        const menuButton = document.querySelector('header img');
        menuButton.addEventListener('click', () => {
            nav.classList.toggle('show');
        });
    </script>
</body>
</html>
