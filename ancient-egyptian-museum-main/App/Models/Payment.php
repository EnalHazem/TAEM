<?php

class Payment {
    public $id;
    public $amount;
    public $paymentDate;
    public $method;
    public $status;
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function processPayment($userId, $amount, $method, $relatedType = null, $relatedId = null) {
        $query = $this->db->prepare("
            INSERT INTO payments (user_id, amount, payment_date, method, status)
            VALUES (:user_id, :amount, NOW(), :method, 'completed')
        ");
        $query->bindParam(':user_id', $userId);
        $query->bindParam(':amount', $amount);
        $query->bindParam(':method', $method);
        $success = $query->execute();

        if ($success && $relatedType && $relatedId) {
            $paymentId = $this->db->lastInsertId();
            $this->linkToEntity($paymentId, $relatedType, $relatedId);
        }

        return $success;
    }

    private function linkToEntity($paymentId, $type, $id) {
        $column = '';
        switch ($type) {
            case 'booking': $column = 'booking_id'; break;
            case 'donation': $column = 'donation_id'; break;
            case 'membership': $column = 'membership_id'; break;
        }

        if ($column) {
            $query = $this->db->prepare("
                UPDATE payments SET $column = :related_id WHERE id = :payment_id
            ");
            $query->bindParam(':related_id', $id);
            $query->bindParam(':payment_id', $paymentId);
            $query->execute();
        }
    }

    public function refund($paymentId) {
        $query = $this->db->prepare("UPDATE payments SET status = 'refunded' WHERE id = :id");
        $query->bindParam(':id', $paymentId);
        return $query->execute();
    }

    public function validatePayment($paymentId) {
        $query = $this->db->prepare("SELECT status FROM payments WHERE id = :id");
        $query->bindParam(':id', $paymentId);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result && $result['status'] === 'completed';
    }
}
