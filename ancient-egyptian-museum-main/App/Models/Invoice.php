<?php

use AncientEgyptianMuseum\Database\DB;

require_once 'DB.php';

class Invoice {
    public  $id;
    public  $issueDate;
    public  $amount;
    public  $status;

    public function generate(): string {
        $db = DB::connect();
        $stmt = $db->prepare("INSERT INTO invoices (issue_date, amount, status) VALUES (?, ?, ?)");
        $stmt->execute([$this->issueDate->format('Y-m-d H:i:s'), $this->amount, $this->status]);
        $this->id = $db->lastInsertId();
        return "Invoice #{$this->id} generated.";
    }

    public function sendInvoice(): void {
       
       
    }
}
?>
















