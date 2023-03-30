# MailerPhp
MailerPhp is a simple PHP class for sending and retrieving emails using SMTP. It provides a simple interface to connect to a mail server, send emails and retrieve emails.

## Installation
You can install MailerPhp using Composer:

## bash
Copy code
composer require username/mailer-php
### Usage
To use MailerPhp, you need to create an instance of the class and set the necessary properties. Here's an example:

## Example
``` php
  require_once 'path/to/vendor/autoload.php';

  use username\MailerPhp;

  $mailer = new MailerPhp();
  $mailer->smtpHost = 'smtp.gmail.com';
  $mailer->smtpPort = 587;
  $mailer->smtpUsername = 'your-email@gmail.com';
  $mailer->smtpPassword = 'your-email-password';
  $mailer->fromEmail = 'your-email@gmail.com';
  $mailer->fromName = 'Your Name'; 
```
To send an email, you can use the sendEmail method:


``` php
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
```
To retrieve emails, you can use the getEmails method:


```php
  $emails = $mailer->getEmails();

  foreach ($emails as $email) {
      echo 'From: ' . $email['headers']['From'] . '<br>';
      echo 'Subject: ' . $email['headers']['Subject'] . '<br>';
      echo 'Date: ' . $email['headers']['Date'] . '<br>';
      echo 'Body: ' . $email['body'] . '<br><br>';
  }
```
The getEmails method returns an array of email messages, each message containing the headers and body of the email.

Options
The getEmails method accepts several options:

``` php
  $emails = $mailer->getEmails([
      'limit' => 10,
      'offset' => 0,
      'search' => 'example.com',
      'sender' => 'sender@example.com'
  ]);
```

 - limit: The maximum number of emails to retrieve.
 - offset: The offset to start retrieving emails from.
 - search: A search term to filter emails by.
 - sender: The email address of the sender to filter emails by.

## License
This code is released under the MIT License.
