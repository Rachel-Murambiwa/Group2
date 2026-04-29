<?php

require_once __DIR__ . '/api.php';

api_cors();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
 
$host = 'localhost';
$db   = 'LoanSystem';
$user = 'root';
$pass = '';
 
$options = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
);
 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, $options);
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()), 500);
}
