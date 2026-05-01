<?php
// 1. HEADERS (Must be at the ABSOLUTE top, before any requires or logic)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. SETUP & DB CONNECTION
date_default_timezone_set('Africa/Accra');
require_once '../../db.php'; // Go up two folders to find the Singleton

try {
    // Safely grab the single connection
    $conn = Database::getInstance();
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB Connection Failed"]);
    exit();
}

// 3. GET DATA FROM REACT
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->phone) && !empty($data->otp)) {
    
    // 4. FETCH USER AND OTP TIMESTAMP
    $query = "SELECT otp_code, otp_created_at FROM users WHERE phone = :phone LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":phone", $data->phone);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user) {
        // CHECK EXPIRATION (5 Minutes)
        $createdAt = strtotime($user['otp_created_at']);
        $now = time();
        $minutesPassed = ($now - $createdAt) / 60;

        if ($minutesPassed > 5) {
            http_response_code(401);
            echo json_encode(["error" => "OTP has expired. Please request a new one."]);
            exit();
        }

        if ($user['otp_code'] === $data->otp) {
            // SUCCESS - Verify the user
            $update = "UPDATE users SET is_verified = 1, otp_code = NULL WHERE phone = :phone";
            $updateStmt = $conn->prepare($update);
            $updateStmt->bindParam(":phone", $data->phone);
            $updateStmt->execute();

            http_response_code(200);
            echo json_encode(["message" => "Account verified!"]);
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Invalid OTP code."]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["error" => "User not found."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Phone and OTP are required."]);
}
?>