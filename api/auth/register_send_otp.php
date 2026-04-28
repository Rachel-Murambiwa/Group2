<?php
// 1. HEADERS - Essential for React to talk to PHP
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. DB CONNECTION
$host = "localhost";
$db_name = "charleedash_db";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// 3. GET DATA FROM REACT
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->phone) && !empty($data->fullName) && !empty($data->password)) {
    
    // Generate fresh 6-digit OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $hashedPassword = password_hash($data->password, PASSWORD_BCRYPT);

    // 4. SMART CHECK: Does phone already exist?
    $checkQuery = "SELECT id, is_verified FROM users WHERE phone = :phone LIMIT 1";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(":phone", $data->phone);
    $checkStmt->execute();
    $existingUser = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        if ($existingUser['is_verified'] == 1) {
            http_response_code(409);
            echo json_encode(["error" => "This number is already verified. Please log in."]);
            exit();
        } else {
            // Update existing unverified user
            $query = "UPDATE users SET 
                        full_name = :name, 
                        alias = :alias, 
                        password = :pass, 
                        otp_code = :otp, 
                        otp_created_at = CURRENT_TIMESTAMP 
                      WHERE phone = :phone";
        }
    } else {
        // Create brand new user
        $query = "INSERT INTO users (full_name, phone, alias, password, otp_code, is_verified, otp_created_at) 
                  VALUES (:name, :phone, :alias, :pass, :otp, 0, CURRENT_TIMESTAMP)";
    }

    $stmt = $conn->prepare($query);
    $stmt->bindParam(":name", $data->fullName);
    $stmt->bindParam(":phone", $data->phone);
    $stmt->bindParam(":alias", $data->alias);
    $stmt->bindParam(":pass", $hashedPassword);
    $stmt->bindParam(":otp", $otp);

    if($stmt->execute()) {
        // Success!
        http_response_code(200);
        echo json_encode([
            "message" => "OTP Sent", 
            "debug_otp" => $otp // Check this in the Network tab to verify!
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