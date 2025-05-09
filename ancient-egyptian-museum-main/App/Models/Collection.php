<?php


class Collection {
    public $id;
    public $title;
    public $description;
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addArtifact($collectionId, $artifactId) {
        $query = $this->db->prepare("INSERT INTO collection_artifacts (collection_id, artifact_id) VALUES (:collection_id, :artifact_id)");
        $query->bindParam(':collection_id', $collectionId);
        $query->bindParam(':artifact_id', $artifactId);
        return $query->execute();
    }

    public function removeArtifact($collectionId, $artifactId) {
        $query = $this->db->prepare("DELETE FROM collection_artifacts WHERE collection_id = :collection_id AND artifact_id = :artifact_id");
        $query->bindParam(':collection_id', $collectionId);
        $query->bindParam(':artifact_id', $artifactId);
        return $query->execute();
    }

    public function listArtifacts($collectionId) {
        $query = $this->db->prepare("
            SELECT a.* FROM artifacts a
            JOIN collection_artifacts ca ON a.id = ca.artifact_id
            WHERE ca.collection_id = :collection_id
        ");
        $query->bindParam(':collection_id', $collectionId);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
