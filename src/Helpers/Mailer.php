<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {

    private PHPMailer $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        // Server settings
        // Should be environment variables in production
        $this->mail->isSMTP();
        $this->mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.mailtrap.io';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $_ENV['SMTP_USER'] ?? 'username';
        $this->mail->Password   = $_ENV['SMTP_PASS'] ?? 'password';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = $_ENV['SMTP_PORT'] ?? 2525;

        // Default Sender
        $fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@medicare.com';
        $fromName  = $_ENV['MAIL_FROM_NAME']    ?? 'MediCare Admin';
        $this->mail->setFrom($fromEmail, $fromName);
    }

    public function sendInvoice(array $user, array $order, string $pdfPath): bool {
        try {
            // Recipients
            $this->mail->addAddress($user['email'], $user['first_name'] . ' ' . $user['last_name']);

            // Attachments
            $this->mail->addAttachment($pdfPath, 'Invoice_' . $order['order_number'] . '.pdf');

            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Your Order Invoice from MediCare - ' . $order['order_number'];
            $this->mail->Body    = "
                <p>Dear " . htmlspecialchars($user['first_name']) . ",</p>
                <p>Thank you for your order! Your payment has been successfully processed.</p>
                <p>Please find attached the tax invoice for your order <b>" . htmlspecialchars($order['order_number']) . "</b>.</p>
                <p>Standard delivery time is estimated at 3-5 business days.</p>
                <p>If you have any questions, feel free to contact us.</p>
                <p><br>Best regards,<br>MediCare Team</p>
            ";

            // Plain text alternative
            $this->mail->AltBody = "Dear " . htmlspecialchars($user['first_name']) . ",\n\nThank you for your order! Please find your invoice attached for order " . htmlspecialchars($order['order_number']) . ".\n\nBest regards,\nMediCare Team";

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            // Log error
            error_log("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
