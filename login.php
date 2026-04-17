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

//Read input
$body     = json_decode(file_get_contents('php://input'), true);
$email    = trim($body['email']    ?? '');
$password = $body['password']      ?? '';

if (empty($email) || empty($password)) {
    http_response_code(422);
    echo json_encode(array('success' => false, 'message' => 'Email and password are required.'));
    exit;
}

//Find user
try {
    $stmt = $pdo->prepare('SELECT * FROM Users WHERE Email = ?');
    $stmt->execute(array($email));
    $user = $stmt->fetch();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error finding user: ' . $e->getMessage()));
    exit;
}

if (!$user) {
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'Invalid email or password.'));
    exit;
}

// Check user is verified
if (!$user['Is_Verified']) {
    http_response_code(403);
    echo json_encode(array('success' => false, 'message' => 'Please verify your email before logging in.'));
    exit;
}

// Check password
if (!password_verify($password, $user['User_Password'])) {
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'Invalid email or password.'));
    exit;
}

//Generate token and save session
try {
    $token     = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
    $createdAt = date('Y-m-d H:i:s');

    $pdo->exec('
        CREATE TABLE IF NOT EXISTS Sessions (
            session_token VARCHAR(64) PRIMARY KEY,
            User_ID       VARCHAR(8)  NOT NULL,
            created_at    DATETIME    NOT NULL,
            expires_at    DATETIME    NOT NULL,
            FOREIGN KEY (User_ID) REFERENCES Users(User_ID)
        )
    ');

    $stmt = $pdo->prepare('DELETE FROM Sessions WHERE User_ID = ?');
    $stmt->execute(array($user['User_ID']));

    $stmt = $pdo->prepare('INSERT INTO Sessions (session_token, User_ID, created_at, expires_at) VALUES (?, ?, ?, ?)');
    $stmt->execute(array($token, $user['User_ID'], $createdAt, $expiresAt));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error creating session: ' . $e->getMessage()));
    exit;
}

//Respond
http_response_code(200);
echo json_encode(array(
    'success' => true,
    'message' => 'Login successful!',
    'token'   => $token,
    'userID'  => $user['User_ID'],
    'name'    => $user['First_Name'],
));