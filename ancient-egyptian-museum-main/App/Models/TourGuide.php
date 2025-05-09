<?php

class TourGuide {
    public $id;
    public $name;
    public $language;
    public $bio;
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function assignToEvent($guideId, $eventId) {
        $query = $this->db->prepare("
            INSERT INTO event_tour_guides (event_id, guide_id)
            VALUES (:event_id, :guide_id)
        ");
        $query->bindParam(':event_id', $eventId);
        $query->bindParam(':guide_id', $guideId);
        return $query->execute();
    }

    public function provideTour($guideId, $userId) {
        $query = $this->db->prepare("
            INSERT INTO tours (guide_id, user_id, tour_date)
            VALUES (:guide_id, :user_id, NOW())
        ");
        $query->bindParam(':guide_id', $guideId);
        $query->bindParam(':user_id', $userId);
        return $query->execute();
    }
}
