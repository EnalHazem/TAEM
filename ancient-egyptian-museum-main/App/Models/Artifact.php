<?php

use AncientEgyptianMuseum\Database\DB;
use App\Models\Model;

class Artifact extends Model{
    public $id;
    public $name;
    public $description;
    public $origin;
    public $period;
    public $material;
    public $imageUrl;


    public  $db = DB::connect();

    public function __construct($db) {
        $this->db = $db;
    }

    public function viewDetails($artifactId) {
        $query = $this->db->prepare("SELECT * FROM artifacts WHERE id = :id");
        $query->bindParam(':id', $artifactId);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function requestLoan($userId, $artifactId) {
        $query = $this->db->prepare("INSERT INTO bookings (user_id, artifact_id, status, booking_date) VALUES (:user_id, :artifact_id, 'pending', NOW())");
        $query->bindParam(':user_id', $userId);
        $query->bindParam(':artifact_id', $artifactId);
        return $query->execute();
    }

    public function updateMetadata($artifactId, $details) {
        $fields = [];
        foreach ($details as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $sql = "UPDATE artifacts SET " . implode(', ', $fields) . " WHERE id = :id";
        $query = $this->db->prepare($sql);
        foreach ($details as $key => $value) {
            $query->bindValue(":$key", $value);
        }
        $query->bindValue(':id', $artifactId);
        return $query->execute();
    }

    public function uploadImage($artifactId, $imageFile) {
        $targetDir = "uploads/artifacts/";
        $fileName = basename($imageFile["name"]);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($imageFile["tmp_name"], $targetFile)) {
            $query = $this->db->prepare("UPDATE artifacts SET image_url = :image_url WHERE id = :id");
            $query->bindParam(':image_url', $targetFile);
            $query->bindParam(':id', $artifactId);
            return $query->execute();
        }
        return false;
    }
}
