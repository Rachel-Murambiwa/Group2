<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed.'));
    exit;
}

require_once 'db.php';

// To generate a new password hash, run: echo password_hash('yourpassword', PASSWORD_DEFAULT);
$adminUsername = 'admin';
$adminPassword = 'Admin@1234'; // Change this to your desired password

//Read input
$body     = json_decode(file_get_contents('php://input'), true);
$username = trim($body['username'] ?? '');
$password = $body['password']      ?? '';

//Validate
if (empty($username) || empty($password)) {
    http_response_code(422);
    echo json_encode(array('success' => false, 'message' => 'Username and password are required.'));
    exit;
}

//Check credentials
if ($username !== $adminUsername || $password !== $adminPassword) {
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'Invalid admin credentials.'));
    exit;
}

//Generate admin token and save to Sessions table
try {
    $token     = 'ADMIN_' . bin2hex(random_bytes(32));
    $createdAt = date('Y-m-d H:i:s');
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

    //Make sure Sessions table exists
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS Sessions (
            session_token VARCHAR(70) PRIMARY KEY,
            User_ID       VARCHAR(8)  NOT NULL,
            created_at    DATETIME    NOT NULL,
            expires_at    DATETIME    NOT NULL
        )
    ');

    // Delete old admin sessions
    $stmt = $pdo->prepare("DELETE FROM Sessions WHERE User_ID = 'ADMIN'");
    $stmt->execute();

    // Save new admin session
    $stmt = $pdo->prepare('INSERT INTO Sessions (session_token, User_ID, created_at, expires_at) VALUES (?, "ADMIN", ?, ?)');
    $stmt->execute(array($token, $createdAt, $expiresAt));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error creating session: ' . $e->getMessage()));
    exit;
}

//Respond
http_response_code(200);
echo json_encode(array(
    'success' => true,
    'message' => 'Admin login successful!',
    'token'   => $token,
));