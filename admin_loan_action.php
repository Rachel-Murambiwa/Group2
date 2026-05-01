<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');
require_once 'admin_auth.php';

$body = api_input();
$loanID = api_value($body, array('loanID', 'loanId', 'loan_id', 'id'), '');
$action = strtolower(trim($body['action'] ?? 'approve'));

if (empty($loanID)) {
    api_json(array('success' => false, 'message' => 'Loan ID is required.'), 422);
}

if (!in_array($action, array('approve', 'reject'))) {
    api_json(array('success' => false, 'message' => 'Action must be approve or reject.'), 422);
}

try {
    $stmt = $pdo->prepare('SELECT * FROM Loan WHERE Loan_ID = ?');
    $stmt->execute(array($loanID));
    $loan = $stmt->fetch();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error finding loan: ' . $e->getMessage()), 500);
}

if (!$loan) {
    api_json(array('success' => false, 'message' => 'Loan not found.'), 404);
}

if ($loan['Loan_Status'] !== 'pending') {
    api_json(array('success' => false, 'message' => 'Only pending loans can be approved or rejected.'), 400);
}

$newStatus = $action === 'approve' ? 'approved' : 'rejected';

try {
    $stmt = $pdo->prepare('UPDATE Loan SET Loan_Status = ? WHERE Loan_ID = ?');
    $stmt->execute(array($newStatus, $loanID));
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error updating loan: ' . $e->getMessage()), 500);
}

api_json(array(
    'success' => true,
    'message' => 'Loan ' . $newStatus . ' successfully.',
    'loan' => api_loan(array_merge($loan, array('Loan_Status' => $newStatus))),
));
