<?php
namespace App\Controllers;

use App\Helpers\Request;
use App\Controllers\BaseController;
use App\Config\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ContactController extends BaseController {

    public function handleContactForm() {
        $data = Request::all();

        $errors = $this->validate($data);
        if (!empty($errors)) {
            $this->jsonErrorResponse('Validation Failed', 400, $errors);
            return;
        }

        // Log the submission to a file as a backup
        $this->logSubmission($data);

        // Send email notification
        try {
            $this->sendEmail($data);
            $this->jsonResponse(['success' => true, 'message' => 'Your message has been sent successfully.']);
        } catch (Exception $e) {
            // Log the error
            error_log('PHPMailer Error: ' . $e->getMessage());
            $this->jsonErrorResponse('Message could not be sent. Please try again later.', 500);
        }
    }

    private function sendEmail(array $data) {
        $mail = new PHPMailer(true);

        // Server settings from Config
        $mail->isSMTP();
        $mail->Host       = Config::get('MAIL_HOST');
        $mail->SMTPAuth   = true;
        $mail->Username   = Config::get('MAIL_USERNAME');
        $mail->Password   = Config::get('MAIL_PASSWORD');
        $mail->SMTPSecure = Config::get('MAIL_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS);
        $mail->Port       = Config::get('MAIL_PORT', 587);

        // Recipients
        $mail->setFrom(Config::get('MAIL_FROM_ADDRESS'), Config::get('MAIL_FROM_NAME'));
        $mail->addAddress(Config::get('MAIL_ADMIN_ADDRESS', Config::get('MAIL_FROM_ADDRESS')), 'Site Admin'); // Send to admin
        $mail->addReplyTo($data['email'], $data['name']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Contact Form Submission from ' . $data['name'];
        $mail->Body    = 'You have received a new message from your website contact form.<br><br>' . 
                         '<b>Name:</b> ' . htmlspecialchars($data['name']) . '<br>' . 
                         '<b>Email:</b> ' . htmlspecialchars($data['email']) . '<br>' . 
                         '<b>Message:</b><br>' . nl2br(htmlspecialchars($data['message']));
        $mail->AltBody = 'Name: ' . $data['name'] . "\n" . 
                         'Email: ' . $data['email'] . "\n" . 
                         'Message: ' . $data['message'];

        $mail->send();
    }

    private function logSubmission(array $data) {
        $logFile = __DIR__ . '/../../../storage/logs/contact_submissions.log';
        
        // Ensure the directory exists
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logMessage = sprintf(
            "[%s] New Contact Form Submission:\nName: %s\nEmail: %s\nMessage: %s\n---\n",
            date('Y-m-d H:i:s'),
            $data['name'] ?? '',
            $data['email'] ?? '',
            $data['message'] ?? ''
        );

        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    private function validate(array $data): array {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email is required';
        }

        if (empty($data['message'])) {
            $errors['message'] = 'Message is required';
        }

        return $errors;
    }
}