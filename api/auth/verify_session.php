<?php
// api/auth/verify_session.php
date_default_timezone_set('Africa/Accra');
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../db.php';

// Extract the token from the Authorization header
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
$token = str_replace('Bearer ', '', $authHeader);

if (empty($token) || $token === 'null') {
    http_response_code(401);
    echo json_encode(["valid" => false, "error" => "No token provided"]);
    exit();
}

try {
    $conn = Database::getInstance();
    
    // Look up the user by their session token
    $stmt = $conn->prepare("SELECT id, token_expiry FROM users WHERE session_token = ? LIMIT 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $currentTime = date('Y-m-d H:i:s');
        
        // Check if the token has expired
        if ($currentTime > $user['token_expiry']) {
            // Delete the expired token from the database
            $clearStmt = $conn->prepare("UPDATE users SET session_token = NULL, token_expiry = NULL WHERE id = ?");
            $clearStmt->execute([$user['id']]);
            
            http_response_code(401);
            echo json_encode(["valid" => false, "error" => "Session expired"]);
        } else {
            // Token is perfectly valid!
            http_response_code(200);
            echo json_encode(["valid" => true]);
        }
    } else {
        http_response_code(401);
        echo json_encode(["valid" => false, "error" => "Invalid token"]);
    }

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["valid" => false, "error" => "Server error"]);
}
?>