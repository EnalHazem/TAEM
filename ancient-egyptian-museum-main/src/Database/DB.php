<?php

namespace AncientEgyptianMuseum\Database;

use PDO;
use PDOException;

class DB
{
    private static $pdo;

    public static function connect()
    {
        if (!self::$pdo) {
            $config = require __DIR__ . '/../../config/database.php';
            try {
                $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
                self::$pdo = new PDO($dsn, $config['username'], $config['password']);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
    public function __construct($driver)
    {
        $this->pdo = $driver->connect(); // تعتمد على درايفر خارجي
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function init()
    {
        // ممكن تستخدمها للتهيئة إن أردت
        return $this->pdo;
    }
}