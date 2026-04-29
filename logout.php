<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');
require_once 'auth.php';

// Delete the session token — user is now logged out
try {
    $stmt = $pdo->prepare('DELETE FROM Sessions WHERE User_ID = ?');
    $stmt->execute(array($loggedInUser['User_ID']));
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error logging out: ' . $e->getMessage()), 500);
}

api_json(array(
    'success' => true,
    'message' => 'Logged out successfully.',
));
