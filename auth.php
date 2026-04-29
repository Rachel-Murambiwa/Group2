<?php

$headers = getallheaders();
$token   = $headers['Authorization'] ?? '';

if (empty($token)) {
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'No token provided. Please log in.'));
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM Sessions WHERE session_token = ?');
    $stmt->execute(array($token));
    $session = $stmt->fetch();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error checking session: ' . $e->getMessage()));
    exit;
}

if (!$session) {
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'Invalid token. Please log in again.'));
    exit;
}

if (new DateTime() > new DateTime($session['expires_at'])) {
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'Token expired. Please log in again.'));
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM Users WHERE User_ID = ?');
    $stmt->execute(array($session['User_ID']));
    $loggedInUser = $stmt->fetch();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error fetching user: ' . $e->getMessage()));
    exit;
}