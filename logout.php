<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');

$token = api_token();

if (empty($token)) {
    api_json(array('success' => false, 'message' => 'No token provided.'), 400);
}

//Delete session
try {
    $stmt = $pdo->prepare('DELETE FROM sessions WHERE session_token = ?');
    $stmt->execute(array($token));
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error logging out: ' . $e->getMessage()), 500);
}

api_json(array(
    'success' => true,
    'message' => 'Logged out successfully.',
));
