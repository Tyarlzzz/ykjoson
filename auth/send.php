<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'eurrielefante.04@gmail.com';
        $mail->Password = 'ubiniwucixmksckd';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->Timeout = 30;
        
        // Recipients
        $mail->setFrom('eurrielefante.04@gmail.com', 'YK Joson System');
        $mail->addAddress('ykjoson@gmail.com');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Forgot Your Password?';
        $mail->Body = "Don't worry, we got you covered! Here is your password<br><br><strong>password123</strong>";
        
        $mail->send();
        echo json_encode([
            'success' => true, 
            'message' => 'Email sent successfully! Check your inbox and spam folder.'
        ]);
        
    } catch (Exception $e) {
        // Return detailed error for debugging
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage(),
            'smtp_error' => $mail->ErrorInfo
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>