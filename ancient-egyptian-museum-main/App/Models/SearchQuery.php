<?php

use AncientEgyptianMuseum\Database\DB;

require_once 'DB.php';

class SearchQuery {
    public int $id;
    public string $queryText;
    public DateTime $searchDate;

    public function execute(): array {
        $this->searchDate = new DateTime();
        $db = DB::connect();
        $stmt = $db->prepare("INSERT INTO search_queries (query_text, search_date) VALUES (?, ?)");
        $stmt->execute([$this->queryText, $this->searchDate->format('Y-m-d H:i:s')]);
        return ["Result 1", "Result 2", "Result 3"];
    }

    public function filter(array $criteria): array {
        return ["Filtered Result 1", "Filtered Result 2"];
    }
}
?>
