<?php
include "../src/mail.class.php";
$mailer = new MailerPhp();
$mailer->smtpHost = 'smtp.gmail.com';
$mailer->smtpPort = 587;
$mailer->smtpUsername = 'your-email@gmail.com';
$mailer->smtpPassword = 'your-email-password';
$mailer->fromEmail = 'your-email@gmail.com';
$mailer->fromName = 'Your Name';

$toEmail = 'recipient-email@example.com';
$toName = 'Recipient Name';
$subject = 'Test Email';
$body = 'This is a test email sent using PHP Mailer.';

$result = $mailer->sendEmail($toEmail, $toName, $subject, $body);

if ($result === true) {
    echo 'Email sent successfully.';
} else {
    echo 'Email could not be sent.';
}
