<?php

use AncientEgyptianMuseum\Database\DB;

require_once 'DB.php';

class Research {
    public int $id;
    public string $topic;
    public string $summary;
    public DateTime $startDate;
    public DateTime $endDate;

    public function addFinding(string $finding): void {
        $db = DB::connect();
        $stmt = $db->prepare("INSERT INTO research_findings (research_id, finding) VALUES (?, ?)");
        $stmt->execute([$this->id, $finding]);
    }

    public function publish(): void {
        $db = DB::connect();
        $stmt = $db->prepare("UPDATE research SET published = 1 WHERE id = ?");
        $stmt->execute([$this->id]);
        echo "Research #{$this->id} published.\n";
    }
}
?>
