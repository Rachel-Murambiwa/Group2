<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed.'));
    exit;
}

require_once 'db.php';
require_once 'auth.php';

// Delete the session token — user is now logged out
try {
    $stmt = $pdo->prepare('DELETE FROM Sessions WHERE User_ID = ?');
    $stmt->execute(array($loggedInUser['User_ID']));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error logging out: ' . $e->getMessage()));
    exit;
}

http_response_code(200);
echo json_encode(array(
    'success' => true,
    'message' => 'Logged out successfully.',
));