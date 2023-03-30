<?php
include "../src/mail.class.php";

$mailer = new MailerPhp();
$mailer->setSmtpHost('smtp.gmail.com');
$mailer->setSmtpPort(587);
$mailer->setSmtpUsername('yourEmail@gmail.com');
$mailer->setSmtpPassword('yourPassword');
$mailer->setFromEmail('yourEmail@gmail.com');
$mailer->setFromName('Your Name');
$mailer->setSmtpSecure('tls');

$toEmail = 'toEmail@mail.com';
$toName = 'Recipient Name';
$subject = 'Test Email';
$body = 'This is a test email sent using Mailer PHP.';

$result = $mailer->sendEmail($toEmail, $toName, $subject, $body);

if ($result === true) {
    echo 'Email sent successfully.';
} else {
    echo 'Email could not be sent.';
}
