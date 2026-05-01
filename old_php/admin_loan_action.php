<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');
require_once 'admin_auth.php';

$body = api_input();
$requestID = api_value($body, array('requestID', 'requestId', 'request_id', 'id'), '');
$action = strtolower(trim($body['action'] ?? 'approve'));

if (empty($requestID)) {
    api_json(array('success' => false, 'message' => 'Request ID is required.'), 422);
}

if (!in_array($action, array('approve', 'reject'))) {
    api_json(array('success' => false, 'message' => 'Action must be approve or reject.'), 422);
}

try {
    $stmt = $pdo->prepare('
        SELECT lr.*, v.amount, v.interest, v.duration 
        FROM loan_requests lr 
        JOIN vaults v ON lr.vault_id = v.id 
        WHERE lr.id = ?
    ');
    $stmt->execute(array($requestID));
    $request = $stmt->fetch();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error finding loan request: ' . $e->getMessage()), 500);
}

if (!$request) {
    api_json(array('success' => false, 'message' => 'Loan request not found.'), 404);
}

if ($request['status'] !== 'pending') {
    api_json(array('success' => false, 'message' => 'Only pending loan requests can be approved or rejected.'), 400);
}

$newStatus = $action === 'approve' ? 'approved' : 'rejected';

try {
    $pdo->beginTransaction();
    
    // Update loan request status
    $stmt = $pdo->prepare('UPDATE loan_requests SET status = ? WHERE id = ?');
    $stmt->execute(array($newStatus, $requestID));
    
    if ($action === 'approve') {
        // Create active contract
        $dueDate = date('Y-m-d', strtotime('+' . $request['duration'] . ' months'));
        
        $stmt = $pdo->prepare('
            INSERT INTO active_contracts (vault_id, borrower_id, due_date, status)
            VALUES (?, ?, ?, "active")
        ');
        $stmt->execute(array($request['vault_id'], $request['borrower_id'], $dueDate));
        
        // Update vault status to active
        $stmt = $pdo->prepare('UPDATE vaults SET status = "active" WHERE id = ?');
        $stmt->execute(array($request['vault_id']));
        
        // Record loan disbursement transaction
        $stmt = $pdo->prepare('
            INSERT INTO transactions (user_id, type, amount, created_at)
            VALUES (?, "loan_disbursed", ?, NOW())
        ');
        $stmt->execute(array($request['borrower_id'], $request['amount']));
    }
    
    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollback();
    api_json(array('success' => false, 'message' => 'Error updating loan request: ' . $e->getMessage()), 500);
}

api_json(array(
    'success' => true,
    'message' => 'Loan request ' . $newStatus . ' successfully.',
    'request' => array_merge($request, array('status' => $newStatus)),
));
