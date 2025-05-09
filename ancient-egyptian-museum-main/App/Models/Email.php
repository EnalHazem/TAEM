<?php

use AncientEgyptianMuseum\Database\DB;

require_once 'DB.php';

class Email {
    public string $address;
    public DateTime $sentDate;

    public function send(string $subject, string $body): void {
        $this->sentDate = new DateTime();
        $db = DB::connect();
        $stmt = $db->prepare("INSERT INTO emails (address, sent_date, subject, body) VALUES (?, ?, ?, ?)");
        $stmt->execute([$this->address, $this->sentDate->format('Y-m-d H:i:s'), $subject, $body]);
        echo "Email sent to {$this->address}.\n";
    }

    public function validateEmail(): bool {
        return filter_var($this->address, FILTER_VALIDATE_EMAIL) !== false;
    }
}
?>
