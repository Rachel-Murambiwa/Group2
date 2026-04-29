<?php
date_default_timezone_set('Africa/Accra');

// 1. HEADERS - Essential for React to talk to PHP
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. DB CONNECTION
$host = "db"; 
$db_name = "charleedash_db";
$username = "root";
$password = "Chacha@1583";

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
        
        // ---- 5. TRIGGER THE SMS ----
        // Format the phone number (change 0... to 233...)
        $formattedPhone = preg_replace('/^0/', '233', $data->phone); 
        
        // Send it!
        sendOTP_SMS($formattedPhone, $otp);
        // -----------------------------

        // Success Response to React
        http_response_code(200);
        echo json_encode([
            "message" => "OTP Sent", 
            "debug_otp" => $otp // Keep this for now so you can test without SMS credits!
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to process registration."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Incomplete data."]);
}

// ---------------------------------------------------------
// Helper function to send SMS via Arkesel API
// ---------------------------------------------------------
function sendOTP_SMS($phone, $otp) {
    // 1. Get these from your SMS provider dashboard
    $apiKey = 'bEdod25DZUJkYkVnc1NnYlpxWWY'; 
    $senderId = 'VaultAuth'; // Sender ID (Max 11 characters)

    $message = "Your CharleeDash+ verification code is: $otp. It expires in 15 minutes. Do not share this code.";

    $url = 'https://sms.arkesel.com/api/v2/sms/send';
    
    $data = [
        'sender' => $senderId,
        'message' => $message,
        'recipients' => [$phone]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'api-key: ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response; 
}
?>