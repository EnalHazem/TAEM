<?php

class Exhibition {
    public $id;
    public $title;
    public $type;
    public $startDate;
    public $endDate;
    public $description;
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function startExhibition($exhibitionId) {
        $query = $this->db->prepare("UPDATE exhibitions SET start_date = CURDATE() WHERE id = :id");
        $query->bindParam(':id', $exhibitionId);
        return $query->execute();
    }

    public function endExhibition($exhibitionId) {
        $query = $this->db->prepare("UPDATE exhibitions SET end_date = CURDATE() WHERE id = :id");
        $query->bindParam(':id', $exhibitionId);
        return $query->execute();
    }

    public function addArtifact($exhibitionId, $artifactId) {
        $query = $this->db->prepare("INSERT INTO exhibition_artifacts (exhibition_id, artifact_id) VALUES (:exhibition_id, :artifact_id)");
        $query->bindParam(':exhibition_id', $exhibitionId);
        $query->bindParam(':artifact_id', $artifactId);
        return $query->execute();
    }

    public function removeArtifact($exhibitionId, $artifactId) {
        $query = $this->db->prepare("DELETE FROM exhibition_artifacts WHERE exhibition_id = :exhibition_id AND artifact_id = :artifact_id");
        $query->bindParam(':exhibition_id', $exhibitionId);
        $query->bindParam(':artifact_id', $artifactId);
        return $query->execute();
    }

    public function getDuration($exhibitionId) {
        $query = $this->db->prepare("SELECT DATEDIFF(end_date, start_date) AS duration FROM exhibitions WHERE id = :id");
        $query->bindParam(':id', $exhibitionId);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['duration'] : null;
    }
}
