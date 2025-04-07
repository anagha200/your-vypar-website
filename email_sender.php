<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendWelcomeEmail($recipientEmail, $employeeName, $employeeId, $password) {
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration for custom domain with SSL/TLS
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com'; // SMTP server for paysing.in (use your email domain's SMTP host)
        $mail->Port = 465; // Port 465 for SSL (use 587 for TLS if necessary)
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = 'info@yourvypar.in'; // Your custom domain email address
        $mail->Password = 'YourVypar@123'; // Password for the custom domain email
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use SSL for encryption (use 'tls' for TLS)

        // Email settings
        $mail->setFrom('info@yourvypar.in', 'YourVypar Team');
        $mail->addAddress($recipientEmail); // Recipient's email address

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to YourVypar';
        $mail->Body = "<p>Dear $employeeName,</p>
                       <p>Welcome to YourVypar! Here are your login credentials:</p>
                       <p>Employee ID: <strong>$employeeId</strong></p>
                       <p>Password: <strong>$password</strong></p>
                       <p>Please change your password after logging in.Here is the link to login http://localhost/try/employee-login.html</p>
                       <p>Best regards,<br>YourVypar Team</p>";

        // Send email
        $mail->send();
        return 'Email sent successfully to ' . $recipientEmail . '.';
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
