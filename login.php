<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');

//Read input
$body     = api_input();
$email    = trim(api_value($body, array('email', 'Email'), ''));
$password = api_value($body, array('password', 'userPassword', 'user_password'), '');

if (empty($email) || empty($password)) {
    api_json(array('success' => false, 'message' => 'Email and password are required.'), 422);
}

//Find user
try {
    $stmt = $pdo->prepare('SELECT * FROM Users WHERE Email = ?');
    $stmt->execute(array($email));
    $user = $stmt->fetch();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error finding user: ' . $e->getMessage()), 500);
}

if (!$user) {
    api_json(array('success' => false, 'message' => 'Invalid email or password.'), 401);
}

// Check user is verified
if (!$user['Is_Verified']) {
    api_json(array('success' => false, 'message' => 'Please verify your email before logging in.'), 403);
}

// Check password
if (!password_verify($password, $user['User_Password'])) {
    api_json(array('success' => false, 'message' => 'Invalid email or password.'), 401);
}

//Generate token and save session
try {
    $token     = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
    $createdAt = date('Y-m-d H:i:s');

    $pdo->exec('
        CREATE TABLE IF NOT EXISTS Sessions (
            session_token VARCHAR(70) PRIMARY KEY,
            User_ID       VARCHAR(8)  NOT NULL,
            created_at    DATETIME    NOT NULL,
            expires_at    DATETIME    NOT NULL
        )
    ');

    $pdo->exec('ALTER TABLE Sessions MODIFY session_token VARCHAR(70) NOT NULL');

    $stmt = $pdo->prepare('DELETE FROM Sessions WHERE User_ID = ?');
    $stmt->execute(array($user['User_ID']));

    $stmt = $pdo->prepare('INSERT INTO Sessions (session_token, User_ID, created_at, expires_at) VALUES (?, ?, ?, ?)');
    $stmt->execute(array($token, $user['User_ID'], $createdAt, $expiresAt));
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error creating session: ' . $e->getMessage()), 500);
}

//Respond
api_json(array(
    'success' => true,
    'message' => 'Login successful!',
    'token'   => $token,
    'userID'  => $user['User_ID'],
    'name'    => $user['First_Name'],
    'user'    => api_user($user),
));
