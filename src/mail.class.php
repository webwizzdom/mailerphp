 <?php
class MailerPhp {
    private string $smtpHost;
    private int $smtpPort;
    private string $smtpUsername;
    private string $smtpPassword;

    public function __construct(string $smtpHost, int $smtpPort, string $smtpUsername, string $smtpPassword) {
        $this->smtpHost = $smtpHost;
        $this->smtpPort = $smtpPort;
        $this->smtpUsername = $smtpUsername;
        $this->smtpPassword = $smtpPassword;
    }

    public function send(string $to, string $subject, string $body, string $from = ''): void {
        $smtpConn = $this->connect();
        $this->sendCommand($smtpConn, "MAIL FROM: <{$this->smtpUsername}>");
        $this->sendCommand($smtpConn, "RCPT TO: <$to>");
        $this->sendCommand($smtpConn, "DATA", 354);
        $this->sendCommand($smtpConn, "From: $from");
        $this->sendCommand($smtpConn, "To: $to");
        $this->sendCommand($smtpConn, "Subject: $subject");
        $this->sendCommand($smtpConn, "");
        $this->sendCommand($smtpConn, $body);
        $this->sendCommand($smtpConn, ".", 250);
        fclose($smtpConn);
    }

    public function retrieve(int $limit = 10, int $offset = 0, string $sender = '', string $searchTerm = ''): array {
        $smtpConn = $this->connect();
        $this->sendCommand($smtpConn, "UID SEARCH ALL");
        $response = $this->getResponse($smtpConn);
        $emailIds = preg_match_all("/\d+/", $response, $matches) ? $matches[0] : [];
        
        if ($sender) {
            $emailIds = array_filter($emailIds, function($emailId) use ($smtpConn, $sender) {
                $this->sendCommand($smtpConn, "UID FETCH $emailId BODY[HEADER.FIELDS (FROM)]");
                $header = $this->getResponse($smtpConn);
                $from = substr($header, strpos($header, ':') + 1);
                return strpos($from, $sender) !== false;
            });
        }
        
        if ($searchTerm) {
            $emailIds = array_filter($emailIds, function($emailId) use ($smtpConn, $searchTerm) {
                $this->sendCommand($smtpConn, "UID SEARCH BODY \"$searchTerm\"");
                $response = $this->getResponse($smtpConn);
                return strpos($response, "$emailId") !== false;
            });
        }
        
        if ($offset > 0) {
            $emailIds = array_slice($emailIds, $offset);
        }
        
        if ($limit > 0) {
            $emailIds = array_slice($emailIds, 0, $limit);
        }
        
        $messages = array_map(function($emailId) use ($smtpConn) {
            $this->sendCommand($smtpConn, "UID FETCH $emailId BODY[TEXT]");
            $body = $this->getResponse($smtpConn);
            $headers = $this->getHeaders($smtpConn, $emailId);
            
            return [
                'id' => $emailId,
                'from' => $headers['From'] ?? '',
                'to' => $headers['To'] ?? '',
                'subject' => $headers['Subject'] ?? '',
                'date' => $headers['Date'] ?? '',
            'body' => $body
        ];
    }, $emailIds);

    fclose($smtpConn);
    return $messages;
}

private function connect(): resource {
    $smtpConn = fsockopen($this->smtpHost, $this->smtpPort, $errno, $errstr);
    $this->getResponse($smtpConn);
    $this->sendCommand($smtpConn, "EHLO $this->smtpHost", 250);
    $this->sendCommand($smtpConn, "AUTH LOGIN", 334);
    $this->sendCommand($smtpConn, base64_encode($this->smtpUsername), 334);
    $this->sendCommand($smtpConn, base64_encode($this->smtpPassword), 235);
    return $smtpConn;
}

private function sendCommand($smtpConn, string $command, int $expectedCode = 0): void {
    fwrite($smtpConn, "$command\r\n");
    $response = $this->getResponse($smtpConn);
    $code = (int)substr($response, 0, 3);
    if ($expectedCode && $code !== $expectedCode) {
        throw new Exception("SMTP Error: $response");
    }
}

private function getResponse($smtpConn): string {
    $response = "";
    while ($str = fgets($smtpConn, 515)) {
        $response .= $str;
        if (substr($str, 3, 1) == " ") {
            break;
        }
    }
    return $response;
}

private function getHeaders($smtpConn, $emailId): array {
    $this->sendCommand($smtpConn, "UID FETCH $emailId BODY[HEADER]");
    $header = $this->getResponse($smtpConn);
    $headers = [];
    foreach (explode("\r\n", $header) as $line) {
        if (strpos($line, ':') !== false) {
            [$key, $value] = explode(':', $line, 2);
            $headers[$key] = trim($value);
        }
    }
    return $headers;
}
}
