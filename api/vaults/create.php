<?php
// api/vaults/create.php

// 1. Native CORS Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit();
}

// 2. Include the Singleton Database Connection
require_once '../db.php';

try {
    $conn = Database::getInstance();
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// 3. Read JSON Payload
$data = json_decode(file_get_contents("php://input"), true);

$userID = isset($data['userID']) ? trim($data['userID']) : (isset($data['id']) ? trim($data['id']) : '');
$amount = isset($data['amount']) ? (float)$data['amount'] : 0;
$interest = isset($data['interest']) ? (float)$data['interest'] : 0;
$duration = isset($data['duration']) ? (int)$data['duration'] : 0;

// 4. Validation
if ($userID === '') {
    http_response_code(401);
    echo json_encode(["error" => "Please log in again before deploying capital."]);
    exit();
}

if ($amount <= 0) {
    http_response_code(422);
    echo json_encode(["error" => "Amount must be greater than zero."]);
    exit();
}

if ($interest < 0 || $interest > 15) {
    http_response_code(422);
    echo json_encode(["error" => "Interest must be between 0 and 15 percent."]);
    exit();
}

if ($duration <= 0) {
    http_response_code(422);
    echo json_encode(["error" => "Duration must be at least 1 day."]);
    exit();
}

try {
    // 5. Check if user is verified
    $stmt = $conn->prepare('SELECT id, alias FROM users WHERE id = ? AND is_verified = 1 LIMIT 1');
    $stmt->execute([$userID]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["error" => "Verified account not found. Please log in again."]);
        exit();
    }

    // 6. Insert into vaults table WITH available_amount
    $stmt = $conn->prepare('
        INSERT INTO vaults (user_id, amount, available_amount, interest, duration, status) 
        VALUES (?, ?, ?, ?, ?, "available")
    ');
    
    // We pass $amount TWICE here: once for `amount`, once for `available_amount`
    $stmt->execute([$userID, $amount, $amount, $interest, $duration]);
    
    $vaultID = $conn->lastInsertId();

    // 7. Success Response
    http_response_code(201);
    echo json_encode([
        'message' => 'Capital deployed successfully.',
        'vault' => [
            'id' => (int)$vaultID,
            'amount' => $amount, // This will be used in React state temporarily
            'interest' => $interest,
            'duration' => $duration,
            'alias' => $user['alias']
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Database error in create.php: " . $e->getMessage());
    echo json_encode(["error" => "Failed to deploy capital due to a server error."]);
}
?>