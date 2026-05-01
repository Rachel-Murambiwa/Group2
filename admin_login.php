<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');

// Admin credentials
$adminUsername = 'admin';
$adminPassword = 'Admin@1234';

//Read input
$body     = api_input();
$username = trim(api_value($body, array('username', 'email'), ''));
$password = api_value($body, array('password'), '');

//Validate
if (empty($username) || empty($password)) {
    api_json(array('success' => false, 'message' => 'Username and password are required.'), 422);
}

//Check credentials
if ($username !== $adminUsername || $password !== $adminPassword) {
    api_json(array('success' => false, 'message' => 'Invalid admin credentials.'), 401);
}

//Generate admin token and save to sessions table
try {
    $token     = 'ADMIN_' . bin2hex(random_bytes(32));
    $createdAt = date('Y-m-d H:i:s');
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

    //Make sure sessions table exists
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS sessions (
            session_token VARCHAR(70) PRIMARY KEY,
            user_id       INT         NULL,
            admin_user    VARCHAR(50) NULL,
            created_at    DATETIME    NOT NULL,
            expires_at    DATETIME    NOT NULL
        )
    ');

    // Delete old admin sessions
    $stmt = $pdo->prepare("DELETE FROM sessions WHERE admin_user = 'admin'");
    $stmt->execute();

    // Save new admin session
    $stmt = $pdo->prepare('INSERT INTO sessions (session_token, admin_user, created_at, expires_at) VALUES (?, "admin", ?, ?)');
    $stmt->execute(array($token, $createdAt, $expiresAt));
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error creating session: ' . $e->getMessage()), 500);
}

//Respond
api_json(array(
    'success' => true,
    'message' => 'Admin login successful!',
    'token'   => $token,
    'admin'   => array('username' => $adminUsername),
));
