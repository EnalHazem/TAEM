<?php

class Event {
    public $id;
    public $title;
    public $dateTime;
    public $location;
    public $capacity;
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function schedule($title, $dateTime, $location, $capacity) {
        $query = $this->db->prepare("
            INSERT INTO events (title, date_time, location, capacity)
            VALUES (:title, :date_time, :location, :capacity)
        ");
        $query->bindParam(':title', $title);
        $query->bindParam(':date_time', $dateTime);
        $query->bindParam(':location', $location);
        $query->bindParam(':capacity', $capacity);
        return $query->execute();
    }

    public function cancel($eventId) {
        $query = $this->db->prepare("DELETE FROM events WHERE id = :id");
        $query->bindParam(':id', $eventId);
        return $query->execute();
    }

    public function checkAvailability($eventId) {
        $query = $this->db->prepare("
            SELECT capacity - COUNT(b.id) AS available
            FROM events e
            LEFT JOIN bookings b ON e.id = b.event_id
            WHERE e.id = :id
            GROUP BY e.capacity
        ");
        $query->bindParam(':id', $eventId);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['available'] : null;
    }

    public function registerParticipant($userId, $eventId, $participants = 1) {
        $available = $this->checkAvailability($eventId);
        if ($available === null || $available < $participants) {
            return false; // Not enough space
        }

        $query = $this->db->prepare("
            INSERT INTO bookings (user_id, event_id, participants, status, booking_date)
            VALUES (:user_id, :event_id, :participants, 'confirmed', NOW())
        ");
        $query->bindParam(':user_id', $userId);
        $query->bindParam(':event_id', $eventId);
        $query->bindParam(':participants', $participants);
        return $query->execute();
    }
}
