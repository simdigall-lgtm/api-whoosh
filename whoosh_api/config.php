<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "whoosh_db";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db);
} catch (Exception $e) {
    header('Content-Type: application/json');
    die(json_encode(["status" => "error", "message" => "Database connection failed: " . $e->getMessage()]));
}