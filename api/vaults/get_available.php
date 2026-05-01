<?php
// api/vaults/get_available.php

// 1. Native CORS Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

date_default_timezone_set('Africa/Accra');

// 2. Include the Singleton Database Connection
require_once '../db.php';

try {
    $conn = Database::getInstance();

    // 3. Query the database using the new available_amount column
    // We alias it as 'amount' so the React frontend maps it correctly.
    // We only select vaults where available_amount is strictly greater than 0.
    $query = "SELECT v.id, v.available_amount AS amount, v.interest, v.duration, u.alias 
              FROM vaults v 
              JOIN users u ON v.user_id = u.id 
              WHERE v.status = 'available' AND v.available_amount > 0
              ORDER BY v.created_at DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $vaults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(["vaults" => $vaults]);

} catch(PDOException $e) {
    http_response_code(500);
    error_log("Database error in get_available.php: " . $e->getMessage());
    echo json_encode(["error" => "Failed to fetch available vaults."]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
}
?>