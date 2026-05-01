<?php

require_once 'api.php';

// Database connection using same credentials as API
$host = "db"; 
$db_name = "charleedash_db";
$username = "root";
$password = "Chacha@1583";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()), 500);
}
