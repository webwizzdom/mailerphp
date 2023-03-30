<?php
class MailerPhp {
  private $smtpHost;
  private $smtpPort;
  private $smtpUsername;
  private $smtpPassword;
  private $fromEmail;
  private $fromName;
  private $smtpSecure;

  public function setSmtpHost($smtpHost) {
      $this->smtpHost = $smtpHost;
  }

  public function setSmtpPort($smtpPort) {
      $this->smtpPort = $smtpPort;
  }

  public function setSmtpUsername($smtpUsername) {
      $this->smtpUsername = $smtpUsername;
  }

  public function setSmtpPassword($smtpPassword) {
      $this->smtpPassword = $smtpPassword;
  }

  public function setFromEmail($fromEmail) {
      $this->fromEmail = $fromEmail;
  }

  public function setFromName($fromName) {
      $this->fromName = $fromName;
  }

  public function setSmtpSecure($smtpSecure) {
      $this->smtpSecure = $smtpSecure;
  }

  public function sendEmail($toEmail, $toName, $subject, $body) {
      $headers = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
      $headers .= "Reply-To: {$this->fromEmail}\r\n";
      $headers .= "Content-type: text/html\r\n";
      $message = "<html><body>{$body}</body></html>";

      $transport = "{$this->smtpSecure}://{$this->smtpHost}:{$this->smtpPort}";
      $options = [
          'ssl' => [
              'verify_peer' => false,
              'verify_peer_name' => false,
          ]
      ];

      $success = false;
      if (mail($toEmail, $subject, $message, $headers)) {
          $success = true;
      }

      return $success;
  }

  public function retrieveEmails($limit = null, $offset = null, $senderEmail = null, $searchTerm = null) {
      // Establish connection to the server
      $connection = imap_open(
          "{$this->smtpHost}:{$this->smtpPort}/{$this->smtpSecure}",
          $this->smtpUsername,
          $this->smtpPassword
      );

      if (!$connection) {
          throw new Exception("Failed to connect to the server");
      }

      // Define the search criteria
      $criteria = "ALL";
      if (!is_null($senderEmail)) {
          $criteria .= " FROM \"{$senderEmail}\"";
      }
      if (!is_null($searchTerm)) {
          $criteria .= " TEXT \"{$searchTerm}\"";
      }

      // Retrieve the emails
      $emails = imap_search($connection, $criteria, SE_UID, "UTF-8");

      if (!$emails) {
          return [];
      }

      // Sort the emails in descending order
      rsort($emails);

      // Apply limit and offset
      if (!is_null($limit) && !is_null($offset)) {
          $emails = array_slice($emails, $offset, $limit);
      } elseif (!is_null($limit)) {
          $emails = array_slice($emails, 0, $limit);
      } elseif (!is_null($offset)) {
          $emails = array_slice($emails, $offset);
      }

      $result = [];

      foreach ($emails as $emailUid) {
          // Retrieve the email data
          $emailData = imap_fetch_overview($connection, $emailUid, FT_UID);
          // Retrieve the email body
          $emailBody = imap_fetchbody($connection, $emailUid, 1);

          // Add the email to the result array
          $result[] = [
              'uid' => $emailData[0]->uid,
              'date' => $emailData[0]->date,
              'subject' => $emailData[0]->subject,
              'from' => $emailData[0]->from,
              'to' => $emailData[0]->to,
              'body' => $emailBody,
          ];
      }

      // Close the connection
      imap_close($connection);

      return $result;
    }
}
