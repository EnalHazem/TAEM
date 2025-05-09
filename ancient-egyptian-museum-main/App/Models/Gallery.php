<?php

class Gallery {
    public $id;
    public $name;
    public $floor;
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function scheduleDisplay($artifactId, $galleryId, $startDate, $endDate) {
        $query = $this->db->prepare("
            INSERT INTO gallery_displays (gallery_id, artifact_id, start_date, end_date)
            VALUES (:gallery_id, :artifact_id, :start_date, :end_date)
        ");
        $query->bindParam(':gallery_id', $galleryId);
        $query->bindParam(':artifact_id', $artifactId);
        $query->bindParam(':start_date', $startDate);
        $query->bindParam(':end_date', $endDate);
        return $query->execute();
    }

    public function allocateArtifact($artifactId, $galleryId) {
        $query = $this->db->prepare("
            INSERT INTO gallery_artifacts (gallery_id, artifact_id)
            VALUES (:gallery_id, :artifact_id)
        ");
        $query->bindParam(':gallery_id', $galleryId);
        $query->bindParam(':artifact_id', $artifactId);
        return $query->execute();
    }

    public function removeArtifact($artifactId, $galleryId) {
        $query = $this->db->prepare("
            DELETE FROM gallery_artifacts
            WHERE gallery_id = :gallery_id AND artifact_id = :artifact_id
        ");
        $query->bindParam(':gallery_id', $galleryId);
        $query->bindParam(':artifact_id', $artifactId);
        return $query->execute();
    }
}
