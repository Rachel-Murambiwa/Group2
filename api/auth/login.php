<?php
// Timezone Sync
date_default_timezone_set('Africa/Accra');

// 1. HEADERS - The CORS Gatekeeper
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. DATABASE CONNECTION
require_once '../db.php';

try {
    $conn = Database::getInstance();
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// 3. GET DATA FROM REACT
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->phone) && !empty($data->password)) {
    
    // 4. FIND THE USER BY PHONE (Explicitly fetching is_admin)
    $query = "SELECT id, alias, phone, password, is_verified, is_admin FROM users WHERE phone = :phone LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":phone", $data->phone);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        
        // 5. CHECK VERIFICATION
        if ($user['is_verified'] == 0) {
            http_response_code(403);
            echo json_encode(["error" => "Account not verified."]);
            exit();
        }

        // 6. VERIFY PASSWORD
        if (password_verify($data->password, $user['password'])) {
            
            // Success! Prepare clean user object for React
            // Ensure is_admin is treated as an integer for the ProtectedRoute check
            $responseData = [
                "id" => $user['id'],
                "alias" => $user['alias'],
                "phone" => $user['phone'],
                "is_admin" => (int)$user['is_admin'] 
            ];
            
            http_response_code(200);
            echo json_encode([
                "message" => "Login successful",
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