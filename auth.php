<?php

$token = api_token();

if (empty($token)) {
    api_json(array('success' => false, 'message' => 'No token provided. Please log in.'), 401);
}

try {
    $stmt = $pdo->prepare('SELECT * FROM sessions WHERE session_token = ?');
    $stmt->execute(array($token));
    $session = $stmt->fetch();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error checking session: ' . $e->getMessage()), 500);
}

if (!$session) {
    api_json(array('success' => false, 'message' => 'Invalid token. Please log in again.'), 401);
}

if (new DateTime() > new DateTime($session['expires_at'])) {
    api_json(array('success' => false, 'message' => 'Token expired. Please log in again.'), 401);
}

try {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute(array($session['user_id']));
    $loggedInUser = $stmt->fetch();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching user: ' . $e->getMessage()), 500);
}
