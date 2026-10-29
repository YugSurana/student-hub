<?php
// ========================================
// Practical 8 - Database Connection (db.php)
// StudentHub - PDO MySQL Connection
// ========================================

$host = "localhost";
$dbname = "studenthub";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );
    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
