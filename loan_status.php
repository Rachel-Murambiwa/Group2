<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed.'));
    exit;
}

require_once 'db.php';
require_once 'auth.php';

//Get loanID
$loanID = $_GET['loanID'] ?? '';

if (empty($loanID)) {
    http_response_code(422);
    echo json_encode(array('success' => false, 'message' => 'Loan ID is required.'));
    exit;
}

//Find the loan
try {
    $stmt = $pdo->prepare('SELECT * FROM Loan WHERE Loan_ID = ?');
    $stmt->execute(array($loanID));
    $loan = $stmt->fetch();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error finding loan: ' . $e->getMessage()));
    exit;
}

if (!$loan) {
    http_response_code(404);
    echo json_encode(array('success' => false, 'message' => 'Loan not found.'));
    exit;
}

//Make sure the loan belongs to this user (borrower or lender)
$userID          = $loggedInUser['User_ID'];
$isBorrower      = $loan['Borrower_ID'] === $userID;
$isLender        = $loan['Lender_ID']   === $userID;

if (!$isBorrower && !$isLender) {
    http_response_code(403);
    echo json_encode(array('success' => false, 'message' => 'You do not have access to this loan.'));
    exit;
}

//Get repayment schedule for this loan
try {
    $stmt = $pdo->prepare('SELECT * FROM RepaymentSchedule WHERE Loan_ID = ? ORDER BY Due_Date ASC');
    $stmt->execute(array($loanID));
    $repayments = $stmt->fetchAll();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error fetching repayments: ' . $e->getMessage()));
    exit;
}

//Get transactions for this loan
try {
    $stmt = $pdo->prepare('SELECT * FROM Transactions WHERE Loan_ID = ? ORDER BY Transaction_Date ASC');
    $stmt->execute(array($loanID));
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error fetching transactions: ' . $e->getMessage()));
    exit;
}

// Calculate total paid so far
$totalPaid     = array_sum(array_column($repayments, 'Amount_Due'));
$amountLeft    = $loan['Amount'] - $totalPaid;

//Respond
http_response_code(200);
echo json_encode(array(
    'success'      => true,
    'loan'         => array(
        'loanID'          => $loan['Loan_ID'],
        'amount'          => $loan['Amount'],
        'purpose'         => $loan['Purpose'],
        'status'          => $loan['Loan_Status'],
        'interestRate'    => $loan['Interest_Rate'],
        'durationMonths'  => $loan['Duration_Months'],
        'dateRequested'   => $loan['Date_Requested'],
        'dateDisbursed'   => $loan['Date_Disbursed'],
        'totalPaid'       => $totalPaid,
        'amountLeft'      => max(0, $amountLeft), // Never show negative
        'role'            => $isBorrower ? 'borrower' : 'lender',
    ),
    'repayments'   => $repayments,
    'transactions' => $transactions,
));