<?php
date_default_timezone_set('Africa/Accra');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = "localhost";
$db_name = "charleedash_db";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // We use a JOIN here to get the Alias of the person who created the vault!
    $query = "SELECT v.id, v.amount, v.interest_rate as interest, v.duration_days as duration, u.alias 
              FROM vaults v 
              JOIN users u ON v.lender_id = u.id 
              WHERE v.status = 'available' 
              ORDER BY v.created_at DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $vaults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(["vaults" => $vaults]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>