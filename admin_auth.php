<?php

$headers     = getallheaders();
$adminToken  = $headers['Authorization'] ?? '';

// Check token was provided
if (empty($adminToken)) {
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'No token provided. Please log in as admin.'));
    exit;
}

// Check token starts with ADMIN_ prefix
if (!str_starts_with($adminToken, 'ADMIN_')) {
    http_response_code(403);
    echo json_encode(array('success' => false, 'message' => 'Access denied. Admin token required.'));
    exit;
}

// Check token exists in Sessions table
try {
    $stmt = $pdo->prepare("SELECT * FROM Sessions WHERE session_token = ? AND User_ID = 'ADMIN'");
    $stmt->execute(array($adminToken));
    $session = $stmt->fetch();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error checking session: ' . $e->getMessage()));
    exit;
}

if (!$session) {
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'Invalid admin token. Please log in again.'));
    exit;
}

// Check token has not expired
if (new DateTime() > new DateTime($session['expires_at'])) {
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'Admin token expired. Please log in again.'));
    exit;
}