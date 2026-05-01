<?php

require_once 'db.php';
api_cors('GET');
api_require_method('GET');
require_once 'auth.php';

try {
    $stmt = $pdo->prepare('
        SELECT v.*, u.full_name as lender_name, u.alias as lender_alias 
        FROM vaults v 
        JOIN users u ON v.user_id = u.id 
        WHERE v.status = "available" AND v.user_id != ?
        ORDER BY v.created_at DESC
    ');
    $stmt->execute(array($loggedInUser['id']));
    $vaults = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching available vaults: ' . $e->getMessage()), 500);
}

api_json(array(
    'success' => true,
    'vaults'  => $vaults,
));
