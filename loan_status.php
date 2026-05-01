<?php

require_once 'db.php';
api_cors('GET');
api_require_method('GET');
require_once 'auth.php';

//Get requestID or contractID
$requestID  = api_value($_GET, array('requestID', 'requestId', 'request_id'), '');
$contractID = api_value($_GET, array('contractID', 'contractId', 'contract_id'), '');

if (!empty($requestID)) {
    //Get loan request status
    try {
        $stmt = $pdo->prepare('
            SELECT lr.*, v.amount, v.interest, v.duration, u.full_name as lender_name 
            FROM loan_requests lr 
            JOIN vaults v ON lr.vault_id = v.id 
            JOIN users u ON v.user_id = u.id 
            WHERE lr.id = ? AND lr.borrower_id = ?
        ');
        $stmt->execute(array($requestID, $loggedInUser['id']));
        $request = $stmt->fetch();
    } catch (PDOException $e) {
        api_json(array('success' => false, 'message' => 'Error finding loan request: ' . $e->getMessage()), 500);
    }

    if (!$request) {
        api_json(array('success' => false, 'message' => 'Loan request not found.'), 404);
    }

    api_json(array(
        'success' => true,
        'type' => 'request',
        'request' => $request,
    ));

} elseif (!empty($contractID)) {
    //Get active contract status
    try {
        $stmt = $pdo->prepare('
            SELECT ac.*, v.amount, v.interest, v.duration, u.full_name as lender_name 
            FROM active_contracts ac 
            JOIN vaults v ON ac.vault_id = v.id 
            JOIN users u ON v.user_id = u.id 
            WHERE ac.id = ? AND ac.borrower_id = ?
        ');
        $stmt->execute(array($contractID, $loggedInUser['id']));
        $contract = $stmt->fetch();
    } catch (PDOException $e) {
        api_json(array('success' => false, 'message' => 'Error finding contract: ' . $e->getMessage()), 500);
    }

    if (!$contract) {
        api_json(array('success' => false, 'message' => 'Contract not found.'), 404);
    }

    //Get transactions for this user
    try {
        $stmt = $pdo->prepare('SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute(array($loggedInUser['id']));
        $transactions = $stmt->fetchAll();
    } catch (PDOException $e) {
        api_json(array('success' => false, 'message' => 'Error fetching transactions: ' . $e->getMessage()), 500);
    }

    api_json(array(
        'success' => true,
        'type' => 'contract',
        'contract' => $contract,
        'transactions' => $transactions,
    ));

} else {
    api_json(array('success' => false, 'message' => 'Request ID or Contract ID is required.'), 422);
}
