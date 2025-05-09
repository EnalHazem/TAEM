<?php

use AncientEgyptianMuseum\Database\DB;

require_once 'DB.php';

class Contact {
    public int $id;
    public string $name;
    public string $email;
    public string $subject;
    public string $message;
    public DateTime $sentAt;

    public function submit(): void {
        $this->sentAt = new DateTime();
        $db = DB::connect();
        $stmt = $db->prepare("INSERT INTO contacts (name, email, subject, message, sent_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$this->name, $this->email, $this->subject, $this->message, $this->sentAt->format('Y-m-d H:i:s')]);
        
    }

    public function respond(string $response): void {
        echo "Responding to {$this->email}: $response\n";
    }
}
?>
