<?php

$adminToken = api_token();

// Check token was provided
if (empty($adminToken)) {
    api_json(array('success' => false, 'message' => 'No token provided. Please log in as admin.'), 401);
}

// Check token starts with ADMIN_ prefix
if (!str_starts_with($adminToken, 'ADMIN_')) {
    api_json(array('success' => false, 'message' => 'Access denied. Admin token required.'), 403);
}

// Check token exists in Sessions table
try {
    $stmt = $pdo->prepare("SELECT * FROM Sessions WHERE session_token = ? AND User_ID = 'ADMIN'");
    $stmt->execute(array($adminToken));
    $session = $stmt->fetch();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error checking session: ' . $e->getMessage()), 500);
}

if (!$session) {
    api_json(array('success' => false, 'message' => 'Invalid admin token. Please log in again.'), 401);
}

// Check token has not expired
if (new DateTime() > new DateTime($session['expires_at'])) {
    api_json(array('success' => false, 'message' => 'Admin token expired. Please log in again.'), 401);
}
