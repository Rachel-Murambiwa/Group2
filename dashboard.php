<?php

require_once 'db.php';
api_cors('GET');
api_require_method('GET');
require_once 'auth.php';

//Get user details
$userInfo = array(
    'id' => $loggedInUser['id'],
    'userID' => $loggedInUser['id'],
    'fullName' => $loggedInUser['full_name'],
    'phone' => $loggedInUser['phone'],
    'email' => $loggedInUser['email'],
    'alias' => $loggedInUser['alias'],
    'is_verified' => (bool) $loggedInUser['is_verified']
);

//Get available vaults (loans this user can request)
try {
    $stmt = $pdo->prepare('
        SELECT v.*, u.full_name as lender_name, u.alias as lender_alias 
        FROM vaults v 
        JOIN users u ON v.user_id = u.id 
        WHERE v.status = "available" AND v.user_id != ?
        ORDER BY v.created_at DESC
    ');
    $stmt->execute(array($loggedInUser['id']));
    $availableVaults = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching available vaults: ' . $e->getMessage()), 500);
}

//Get user's vaults (loans they're offering)
try {
    $stmt = $pdo->prepare('SELECT * FROM vaults WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute(array($loggedInUser['id']));
    $userVaults = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching user vaults: ' . $e->getMessage()), 500);
}

//Get loan requests made by this user
try {
    $stmt = $pdo->prepare('
        SELECT lr.*, v.amount, v.interest, v.duration, u.full_name as lender_name 
        FROM loan_requests lr 
        JOIN vaults v ON lr.vault_id = v.id 
        JOIN users u ON v.user_id = u.id 
        WHERE lr.borrower_id = ? 
        ORDER BY lr.created_at DESC
    ');
    $stmt->execute(array($loggedInUser['id']));
    $loanRequests = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching loan requests: ' . $e->getMessage()), 500);
}

//Get active contracts where user is borrower
try {
    $stmt = $pdo->prepare('
        SELECT ac.*, v.amount, v.interest, v.duration, u.full_name as lender_name 
        FROM active_contracts ac 
        JOIN vaults v ON ac.vault_id = v.id 
        JOIN users u ON v.user_id = u.id 
        WHERE ac.borrower_id = ? 
        ORDER BY ac.due_date ASC
    ');
    $stmt->execute(array($loggedInUser['id']));
    $activeLoans = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching active loans: ' . $e->getMessage()), 500);
}

//Get user's transactions
try {
    $stmt = $pdo->prepare('SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute(array($loggedInUser['id']));
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching transactions: ' . $e->getMessage()), 500);
}

//Respond
api_json(array(
    'success'        => true,
    'user'           => $userInfo,
    'availableVaults' => $availableVaults,
    'userVaults'     => $userVaults,
    'loanRequests'   => $loanRequests,
    'activeLoans'    => $activeLoans,
    'transactions'   => $transactions,
));
