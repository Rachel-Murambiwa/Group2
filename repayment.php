<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');
require_once 'auth.php';

//Read input
$body       = api_input();
$contractID = api_value($body, array('contractID', 'contractId', 'contract_id', 'id'), '');
$amount     = api_value($body, array('amount', 'paymentAmount', 'payment_amount'), '');

//Validate
$errors = array();
if (empty($contractID)) $errors[] = 'Contract ID is required.';
if (empty($amount))     $errors[] = 'Payment amount is required.';
if ($amount <= 0)       $errors[] = 'Amount must be greater than zero.';

if (!empty($errors)) {
    api_json(array('success' => false, 'message' => implode(' ', $errors), 'errors' => $errors), 422);
}

//Find the active contract
try {
    $stmt = $pdo->prepare('
        SELECT ac.*, v.amount as loan_amount, v.interest, v.duration 
        FROM active_contracts ac 
        JOIN vaults v ON ac.vault_id = v.id 
        WHERE ac.id = ? AND ac.borrower_id = ? AND ac.status = "active"
    ');
    $stmt->execute(array($contractID, $loggedInUser['id']));
    $contract = $stmt->fetch();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error finding contract: ' . $e->getMessage()), 500);
}

if (!$contract) {
    api_json(array('success' => false, 'message' => 'Active contract not found.'), 404);
}

// Check if repayment is late (past due date)
$dueDate = new DateTime($contract['due_date']);
$today   = new DateTime();
$isLate  = $today > $dueDate;

//Record repayment transaction
try {
    $transactionType = $isLate ? 'repayment_late' : 'repayment';
    
    $stmt = $pdo->prepare('
        INSERT INTO transactions (user_id, type, amount, created_at)
        VALUES (?, ?, ?, NOW())
    ');
    $stmt->execute(array($loggedInUser['id'], $transactionType, $amount));
    $transactionId = $pdo->lastInsertId();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error recording transaction: ' . $e->getMessage()), 500);
}

// Calculate total amount due (principal + interest)
$totalDue = $contract['loan_amount'] * (1 + ($contract['interest'] / 100));

// Check if loan is fully paid
$fullyPaid = $amount >= $totalDue;

if ($fullyPaid) {
    try {
        $stmt = $pdo->prepare('UPDATE active_contracts SET status = "completed" WHERE id = ?');
        $stmt->execute(array($contractID));
        
        // Update vault status back to available if needed
        $stmt = $pdo->prepare('UPDATE vaults SET status = "available" WHERE id = ?');
        $stmt->execute(array($contract['vault_id']));
    } catch (PDOException $e) {
        api_json(array('success' => false, 'message' => 'Error updating contract status: ' . $e->getMessage()), 500);
    }
}

//Respond
api_json(array(
    'success'       => true,
    'message'       => $fullyPaid ? 'Loan fully repaid!' : 'Payment recorded successfully!',
    'contractID'    => $contractID,
    'amountPaid'    => $amount,
    'totalDue'      => $totalDue,
    'fullyPaid'     => $fullyPaid,
    'isLate'        => $isLate,
    'transactionId' => $transactionId,
));
