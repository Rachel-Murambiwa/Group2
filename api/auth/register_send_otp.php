<?php
// 1. HEADERS - Essential for React to talk to PHP
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle pre-flight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. DB CONNECTION & SETUP
require_once '../db.php';
date_default_timezone_set('Africa/Accra');

try {
    // Get the single, secure database connection instance
    $conn = Database::getInstance();
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// 3. GET DATA FROM REACT
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->phone) && !empty($data->fullName) && !empty($data->password)) {
    
    // Generate fresh 6-digit OTP[cite: 6]
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $hashedPassword = password_hash($data->password, PASSWORD_BCRYPT);

    // Normalize phone number by removing any "+" signs for database storage
    $cleanPhone = str_replace('+', '', $data->phone);

    // 4. SMART CHECK: Does phone already exist?[cite: 6]
    $checkStmt = $conn->prepare("SELECT id, is_verified FROM users WHERE phone = :phone LIMIT 1");
    $checkStmt->execute([':phone' => $cleanPhone]);
    $existingUser = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        if ($existingUser['is_verified'] == 1) {
            http_response_code(409); // 409 Conflict
            echo json_encode(["error" => "This number is already verified. Please log in."]);
            exit();
        } else {
            // Update existing unverified user with new credentials and OTP[cite: 6]
            $query = "UPDATE users SET 
                        full_name = :name, 
                        alias = :alias, 
                        password = :pass, 
                        otp_code = :otp, 
                        otp_created_at = CURRENT_TIMESTAMP 
                      WHERE phone = :phone";
        }
    } else {
        // Create brand new user[cite: 6]
        $query = "INSERT INTO users (full_name, phone, alias, password, otp_code, is_verified, otp_created_at) 
                  VALUES (:name, :phone, :alias, :pass, :otp, 0, CURRENT_TIMESTAMP)";
    }

    // 5. EXECUTE THE QUERY
    $stmt = $conn->prepare($query);
    $params = [
        ':name' => $data->fullName,
        ':phone' => $cleanPhone,
        ':alias' => $data->alias,
        ':pass' => $hashedPassword,
        ':otp' => $otp
    ];

    if($stmt->execute($params)) {
        // Success Response to React
        // Return the OTP so the frontend can generate the free WhatsApp link
        http_response_code(200);
        echo json_encode([
            "message" => "OTP Prepared", 
            "otp" => $otp 
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to process registration."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Incomplete data."]);
}
?>