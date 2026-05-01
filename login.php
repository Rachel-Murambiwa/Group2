<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');

//Read input
$body     = api_input();
$phone    = trim(api_value($body, array('phone', 'phoneNumber', 'phone_number'), ''));
$email    = trim(api_value($body, array('email', 'Email'), ''));
$password = api_value($body, array('password', 'userPassword', 'user_password'), '');

// Allow login with either phone or email
$loginField = !empty($phone) ? $phone : $email;

if (empty($loginField) || empty($password)) {
    api_json(array('success' => false, 'message' => 'Phone/email and password are required.'), 422);
}

//Find user by phone or email
try {
    if (!empty($phone)) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE phone = ?');
        $stmt->execute(array($phone));
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute(array($email));
    }
    $user = $stmt->fetch();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error finding user: ' . $e->getMessage()), 500);
}

if (!$user) {
    api_json(array('success' => false, 'message' => 'Invalid credentials.'), 401);
}

// Check user is verified
if (!$user['is_verified']) {
    api_json(array('success' => false, 'message' => 'Please verify your phone before logging in.'), 403);
}

// Check password
if (!password_verify($password, $user['password'])) {
    api_json(array('success' => false, 'message' => 'Invalid credentials.'), 401);
}

//Generate token and save session
try {
    $token     = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
    $createdAt = date('Y-m-d H:i:s');

    // Create sessions table if not exists
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS sessions (
            session_token VARCHAR(70) PRIMARY KEY,
            user_id       INT         NOT NULL,
            created_at    DATETIME    NOT NULL,
            expires_at    DATETIME    NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ');

    $stmt = $pdo->prepare('DELETE FROM sessions WHERE user_id = ?');
    $stmt->execute(array($user['id']));

    $stmt = $pdo->prepare('INSERT INTO sessions (session_token, user_id, created_at, expires_at) VALUES (?, ?, ?, ?)');
    $stmt->execute(array($token, $user['id'], $createdAt, $expiresAt));
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error creating session: ' . $e->getMessage()), 500);
}

//Respond
api_json(array(
    'success' => true,
    'message' => 'Login successful!',
    'token'   => $token,
    'userID'  => $user['id'],
    'name'    => $user['full_name'],
    'user'    => array(
        'id' => $user['id'],
        'userID' => $user['id'],
        'fullName' => $user['full_name'],
        'phone' => $user['phone'],
        'email' => $user['email'],
        'alias' => $user['alias'],
        'is_verified' => (bool) $user['is_verified']
    ),
));
