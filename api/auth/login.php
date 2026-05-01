<?php
// api/auth/login.php
date_default_timezone_set('Africa/Accra');
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../db.php';

try {
    $conn = Database::getInstance();
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->phone) && !empty($data->password)) {
    
    // --- NEW SMART MATCHER LOGIC ---
    // 1. Clean the incoming phone number of any "+" signs
    $cleanPhone = str_replace('+', '', $data->phone);
    $altPhone = $cleanPhone;

    // 2. Generate the alternative format for matching legacy data
    // If they typed 233..., create a 0... version
    if (strpos($cleanPhone, '233') === 0 && strlen($cleanPhone) === 12) {
        $altPhone = '0' . substr($cleanPhone, 3);
    } 
    // If they typed 0..., create a 233... version
    elseif (strpos($cleanPhone, '0') === 0 && strlen($cleanPhone) === 10) {
        $altPhone = '233' . substr($cleanPhone, 1);
    }
    
    // 3. Query the database checking BOTH formats
    $query = "SELECT id, alias, phone, password, is_verified, is_admin FROM users WHERE phone = :phone OR phone = :altPhone LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":phone", $cleanPhone);
    $stmt->bindParam(":altPhone", $altPhone);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Verification Check
        if ($user['is_verified'] == 0) {
            http_response_code(403);
            echo json_encode(["error" => "Account not verified."]);
            exit();
        }

        if (password_verify($data->password, $user['password'])) {
            
            // --- EXISTING SESSION LOGIC ---
            // 1. Generate a secure, random token
            $sessionToken = bin2hex(random_bytes(32)); 
            
            // 2. Set expiration for 2 hours from now
            $expiry = date('Y-m-d H:i:s', strtotime('+2 hours'));

            // 3. Save the token to the database
            $updateStmt = $conn->prepare("UPDATE users SET session_token = ?, token_expiry = ? WHERE id = ?");
            $updateStmt->execute([$sessionToken, $expiry, $user['id']]);

            // Prepare clean user object for React
            $responseData = [
                "id" => $user['id'],
                "alias" => $user['alias'],
                "phone" => $user['phone'],
                "is_admin" => (int)$user['is_admin'] 
            ];
            
            http_response_code(200);
            echo json_encode([
                "message" => "Login successful",
                "token" => $sessionToken, // Send the REAL token to React!
                "user" => $responseData 
            ]);
            
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Incorrect password."]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Account not found."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Incomplete data."]);
}
?>