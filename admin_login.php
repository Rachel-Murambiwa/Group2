<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');

// To generate a new password hash, run: echo password_hash('yourpassword', PASSWORD_DEFAULT);
$adminUsername = 'admin';
$adminPassword = 'Admin@1234'; // Change this to your desired password

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

    $pdo->exec('ALTER TABLE Sessions MODIFY session_token VARCHAR(70) NOT NULL');

    // Delete old admin sessions
    $stmt = $pdo->prepare("DELETE FROM Sessions WHERE User_ID = 'ADMIN'");
    $stmt->execute();

    // Save new admin session
    $stmt = $pdo->prepare('INSERT INTO Sessions (session_token, User_ID, created_at, expires_at) VALUES (?, "ADMIN", ?, ?)');
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
