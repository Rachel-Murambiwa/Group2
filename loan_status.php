<?php

require_once 'db.php';
api_cors('GET');
api_require_method('GET');
require_once 'auth.php';

//Get loanID
$loanID = api_value($_GET, array('loanID', 'loanId', 'loan_id', 'id'), '');

if (empty($loanID)) {
    api_json(array('success' => false, 'message' => 'Loan ID is required.'), 422);
}

//Find the loan
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

//Make sure the loan belongs to this user (borrower or lender)
$userID          = $loggedInUser['User_ID'];
$isBorrower      = $loan['Borrower_ID'] === $userID;
$isLender        = $loan['Lender_ID']   === $userID;

if (!$isBorrower && !$isLender) {
    api_json(array('success' => false, 'message' => 'You do not have access to this loan.'), 403);
}

//Get repayment schedule for this loan
try {
    $stmt = $pdo->prepare('SELECT * FROM RepaymentSchedule WHERE Loan_ID = ? ORDER BY Due_Date ASC');
    $stmt->execute(array($loanID));
    $repayments = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching repayments: ' . $e->getMessage()), 500);
}

//Get transactions for this loan
try {
    $stmt = $pdo->prepare('SELECT * FROM Transactions WHERE Loan_ID = ? ORDER BY Transaction_Date ASC');
    $stmt->execute(array($loanID));
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching transactions: ' . $e->getMessage()), 500);
}

// Calculate total paid so far
$totalPaid     = array_sum(array_column($repayments, 'Amount_Due'));
$amountLeft    = $loan['Amount'] - $totalPaid;

//Respond
api_json(array(
    'success'      => true,
    'loan'         => array_merge(api_loan($loan), array(
        'totalPaid'       => $totalPaid,
        'amountLeft'      => max(0, $amountLeft), // Never show negative
        'role'            => $isBorrower ? 'borrower' : 'lender',
    )),
    'repayments'   => api_repayments($repayments),
    'transactions' => api_transactions($transactions),
));
