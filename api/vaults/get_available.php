<?php
// 1. Native CORS Headers (Must be at the absolute top)
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
    // Securely grab the connection without exposing passwords in this file
    $conn = Database::getInstance();

    // 3. Query the database using the correct 3NF schema columns
    // (vaults table uses 'user_id', 'interest', and 'duration' now)
    $query = "SELECT v.id, v.amount, v.interest, v.duration, u.alias 
              FROM vaults v 
              JOIN users u ON v.user_id = u.id 
              WHERE v.status = 'available' 
              ORDER BY v.created_at DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    // Fetch all available vaults as an associative array
    $vaults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Send the array back to your React frontend
    http_response_code(200);
    echo json_encode(["vaults" => $vaults]);

} catch(PDOException $e) {
    // Hide the exact SQL error from the frontend for security, but log it for debugging
    http_response_code(500);
    error_log("Database error in get_available.php: " . $e->getMessage());
    echo json_encode(["error" => "Failed to fetch available vaults."]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
}
?>