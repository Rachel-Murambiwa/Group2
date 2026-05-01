<?php
// 1. CORS Headers (Absolute top, before ANYTHING else!)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Catch the browser's preflight 'OPTIONS' request and approve it immediately
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. Include the Database Singleton
require_once '../db.php';

// 3. Read the JSON payload from React
$data = json_decode(file_get_contents("php://input"));

// 4. Input Validation: Check if required fields exist
if(empty($data->userID) || empty($data->amount) || empty($data->interest) || empty($data->duration)) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Incomplete data provided. Please fill out all fields."]);
    exit();
}

// 5. Strict Type Validation
$userID = filter_var($data->userID, FILTER_VALIDATE_INT);
$amount = filter_var($data->amount, FILTER_VALIDATE_FLOAT);
$interest = filter_var($data->interest, FILTER_VALIDATE_FLOAT);
$duration = filter_var($data->duration, FILTER_VALIDATE_INT);

// If filter_var fails, it returns false. Let's catch bad data.
if ($userID === false || $amount === false || $interest === false || $duration === false) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid data formats. Please ensure numbers are correct."]);
    exit();
}

// Check boundaries
if ($amount <= 0 || $duration < 1 || $interest < 0 || $interest > 15) {
    http_response_code(400);
    echo json_encode(["error" => "Values are out of allowed ranges."]);
    exit();
}

// 6. Database Insertion (Prepared Statements to prevent SQL Injection)
try {
    // Get the connection from your Singleton
    $conn = Database::getInstance();
    
    // The SQL query using placeholders (:user_id, :amount, etc.)
    $sql = "INSERT INTO vaults (user_id, amount, interest, duration, status) 
            VALUES (:user_id, :amount, :interest, :duration, 'available')";
            
    $stmt = $conn->prepare($sql);
    
    // Bind the validated data to the placeholders safely
    $stmt->bindParam(':user_id', $userID);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':interest', $interest);
    $stmt->bindParam(':duration', $duration);
    
    // Execute the query
    $stmt->execute();
    
    // Send success response back to React
    http_response_code(201); // 201 Created
    echo json_encode([
        "status" => "success",
        "message" => "Capital deployed successfully. Your vault is now active."
    ]);

} catch(PDOException $e) {
    // If the database crashes, send a 500 error but hide the exact SQL error from the user
    http_response_code(500); // Internal Server Error
    
    // You can check this error in your Docker logs!
    error_log("Database Error in create.php: " . $e->getMessage()); 
    
    echo json_encode(["error" => "An internal server error occurred while creating the vault."]);
}
?>