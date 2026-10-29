<?php
// ========================================
// MySQLi Database Connection
// StudentHub - Object-Oriented MySQLi API
// ========================================

$host = "localhost";
$username = "root";
$password = "";
$dbname = "studenthub";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $dbname
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
