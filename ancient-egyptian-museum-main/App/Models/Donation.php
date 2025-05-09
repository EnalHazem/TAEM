<?php

class Donation {
    public $id;
    public $amount;
    public $donatedAt;
    public $paymentMethod;
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function processDonation($userId, $programId, $amount, $paymentMethod) {
        $query = $this->db->prepare("
            INSERT INTO donations (user_id, program_id, amount, donated_at, payment_method)
            VALUES (:user_id, :program_id, :amount, NOW(), :payment_method)
        ");
        $query->bindParam(':user_id', $userId);
        $query->bindParam(':program_id', $programId);
        $query->bindParam(':amount', $amount);
        $query->bindParam(':payment_method', $paymentMethod);
        return $query->execute();
    }

    public function refund($donationId) {
        
        $query = $this->db->prepare("UPDATE donations SET amount = 0 WHERE id = :id");
        $query->bindParam(':id', $donationId);
        return $query->execute();
    }

    public function getDonorDetails($donationId) {
        $query = $this->db->prepare("
            SELECT u.id, u.name, u.email
            FROM users u
            JOIN donations d ON u.id = d.user_id
            WHERE d.id = :id
        ");
        $query->bindParam(':id', $donationId);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }
}
