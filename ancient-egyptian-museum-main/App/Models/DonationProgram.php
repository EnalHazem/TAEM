<?php

class DonationProgram {
    public $id;
    public $name;
    public $description;
    public $goalAmount;
    public $active;
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function startProgram($name, $description, $goalAmount) {
        $query = $this->db->prepare("
            INSERT INTO donation_programs (name, description, goal_amount, active)
            VALUES (:name, :description, :goal_amount, 1)
        ");
        $query->bindParam(':name', $name);
        $query->bindParam(':description', $description);
        $query->bindParam(':goal_amount', $goalAmount);
        return $query->execute();
    }

    public function endProgram($programId) {
        $query = $this->db->prepare("UPDATE donation_programs SET active = 0 WHERE id = :id");
        $query->bindParam(':id', $programId);
        return $query->execute();
    }

    public function updateGoal($programId, $amount) {
        $query = $this->db->prepare("UPDATE donation_programs SET goal_amount = :amount WHERE id = :id");
        $query->bindParam(':amount', $amount);
        $query->bindParam(':id', $programId);
        return $query->execute();
    }

    public function isActive($programId) {
        $query = $this->db->prepare("SELECT active FROM donation_programs WHERE id = :id");
        $query->bindParam(':id', $programId);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result ? (bool)$result['active'] : false;
    }
}
