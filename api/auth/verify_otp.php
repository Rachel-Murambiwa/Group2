<?php
date_default_timezone_set('Africa/Accra');
// 1. HEADERS (Must be at the very top)
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
    echo json_encode(["error" => "DB Connection Failed"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->phone) && !empty($data->otp)) {
    
    // 3. FETCH USER AND OTP TIMESTAMP
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

            echo json_encode(["message" => "Account verified!"]);
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Invalid OTP code."]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["error" => "User not found."]);
    }
}
?>