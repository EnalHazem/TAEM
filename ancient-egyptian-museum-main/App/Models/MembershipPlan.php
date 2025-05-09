<?php

class MembershipPlan {
    public $id;
    public $name;
    public $benefits;
    public $price;
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function isActive($planId) {
        // Assuming a plan is active if it exists and has a non-zero price
        $query = $this->db->prepare("SELECT COUNT(*) FROM membership_plans WHERE id = :id AND price > 0");
        $query->bindParam(':id', $planId);
        $query->execute();
        return $query->fetchColumn() > 0;
    }

    public function calculatePrice($planId, $duration) {
        $query = $this->db->prepare("SELECT price FROM membership_plans WHERE id = :id");
        $query->bindParam(':id', $planId);
        $query->execute();
        $plan = $query->fetch(PDO::FETCH_ASSOC);

        if ($plan) {
            return $plan['price'] * $duration;
        }

        return null;
    }

    public function listBenefits($planId) {
        $query = $this->db->prepare("SELECT benefits FROM membership_plans WHERE id = :id");
        $query->bindParam(':id', $planId);
        $query->execute();
        $plan = $query->fetch(PDO::FETCH_ASSOC);

        if ($plan) {
            return explode(',', $plan['benefits']); // Assuming benefits are stored as comma-separated values
        }

        return [];
    }
}
