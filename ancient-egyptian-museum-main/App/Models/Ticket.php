<?php

class Ticket {
    public $id;
    public $issueDate;
    public $price;
    public $type;
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function validate($ticketId) {
        $query = $this->db->prepare("SELECT * FROM tickets WHERE id = :id");
        $query->bindParam(':id', $ticketId);
        $query->execute();
        $ticket = $query->fetch(PDO::FETCH_ASSOC);

        return $ticket ? true : false;
    }

    public function print($ticketId) {
        $query = $this->db->prepare("
            SELECT t.*, e.title AS event_title, e.date_time
            FROM tickets t
            JOIN events e ON t.event_id = e.id
            WHERE t.id = :id
        ");
        $query->bindParam(':id', $ticketId);
        $query->execute();
        $ticket = $query->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) return false;

        // Simulate PDF generation (replace with Dompdf/TCPDF in real use)
        $pdfContent = "
            Ticket ID: {$ticket['id']}\n
            Event: {$ticket['event_title']}\n
            Date: {$ticket['date_time']}\n
            Type: {$ticket['type']}\n
            Price: {$ticket['price']}\n
            Issued: {$ticket['issue_date']}
        ";

        // Save to file or return as string
        file_put_contents("ticket_{$ticket['id']}.txt", $pdfContent); // Simulated PDF
        return "ticket_{$ticket['id']}.txt";
    }
}
