<?php

use AncientEgyptianMuseum\Database\DB;

require_once 'DB.php';

class InfoPage {
    public int $id;
    public string $slug;
    public string $title;
    public string $content;

    public function publish(): void {
        $db = DB::connect();
        $stmt = $db->prepare("INSERT INTO info_pages (slug, title, content) VALUES (?, ?, ?)");
        $stmt->execute([$this->slug, $this->title, $this->content]);
        $this->id = $db->lastInsertId();
    }

    public function updateContent(string $newContent): void {
        $db = DB::connect();
        $stmt = $db->prepare("UPDATE info_pages SET content = ? WHERE id = ?");
        $stmt->execute([$newContent, $this->id]);
        $this->content = $newContent;
    }

    public function archive(): void {
        $db = DB::connect();
        $stmt = $db->prepare("UPDATE info_pages SET archived = 1 WHERE id = ?");
        $stmt->execute([$this->id]);
    }

    public function getSlug(): string {
        return $this->slug;
    }
}
?>
