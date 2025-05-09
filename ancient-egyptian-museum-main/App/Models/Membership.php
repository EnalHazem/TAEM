<?php

class Membership {
    public $id;
    public $startDate;
    public $endDate;
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function activate($userId, $planId, $durationMonths) {
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime("+$durationMonths months"));

        $query = $this->db->prepare("
            INSERT INTO memberships (user_id, plan_id, start_date, end_date)
            VALUES (:user_id, :plan_id, :start_date, :end_date)
        ");
        $query->bindParam(':user_id', $userId);
        $query->bindParam(':plan_id', $planId);
        $query->bindParam(':start_date', $startDate);
        $query->bindParam(':end_date', $endDate);
        return $query->execute();
    }

    public function renew($membershipId, $periodMonths) {
        $query = $this->db->prepare("
            UPDATE memberships
            SET end_date = DATE_ADD(end_date, INTERVAL :months MONTH)
            WHERE id = :id
        ");
        $query->bindParam(':months', $periodMonths);
        $query->bindParam(':id', $membershipId);
        return $query->execute();
    }

    public function cancel($membershipId) {
        $query = $this->db->prepare("DELETE FROM memberships WHERE id = :id");
        $query->bindParam(':id', $membershipId);
        return $query->execute();
    }

    public function isValid($membershipId) {
        $query = $this->db->prepare("
            SELECT end_date FROM memberships WHERE id = :id
        ");
        $query->bindParam(':id', $membershipId);
        $query->execute();
        $membership = $query->fetch(PDO::FETCH_ASSOC);

        if ($membership) {
            return strtotime($membership['end_date']) >= strtotime(date('Y-m-d'));
        }

        return false;
    }
}
