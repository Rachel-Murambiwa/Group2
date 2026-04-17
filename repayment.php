<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed.'));
    exit;
}

require_once 'db.php';
require_once 'auth.php';
require_once 'credit_score.php';

//Read input
$body       = json_decode(file_get_contents('php://input'), true);
$loanID     = $body['loanID']     ?? '';
$amountPaid = $body['amountPaid'] ?? '';

//Validate
$errors = array();
if (empty($loanID))     $errors[] = 'Loan ID is required.';
if (empty($amountPaid)) $errors[] = 'Amount paid is required.';
if ($amountPaid <= 0)   $errors[] = 'Amount must be greater than zero.';

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(array('success' => false, 'errors' => $errors));
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

// Make sure this is the borrower's loan
if ($loan['Borrower_ID'] !== $loggedInUser['User_ID']) {
    http_response_code(403);
    echo json_encode(array('success' => false, 'message' => 'This is not your loan.'));
    exit;
}

// Check loan is active
if ($loan['Loan_Status'] !== 'disbursed') {
    http_response_code(400);
    echo json_encode(array('success' => false, 'message' => 'Only disbursed loans can be repaid.'));
    exit;
}

// Check if repayment is late (past the loan duration)
$dateDisbursed = new DateTime($loan['Date_Disbursed']);
$dueDate       = clone $dateDisbursed;
$dueDate->modify('+' . $loan['Duration_Months'] . ' months');
$today         = new DateTime();
$isLate        = $today > $dueDate;

//Save repayment and transaction
try {
    $stmt        = $pdo->query('SELECT MAX(Repayment_ID) AS maxID FROM RepaymentSchedule');
    $row         = $stmt->fetch();
    $repaymentID = ($row['maxID'] ?? 0) + 1;
    $todayStr    = date('Y-m-d');

    // Mark as late if past due date, otherwise paid
    $repaymentStatus = $isLate ? 'late' : 'paid';

    $stmt = $pdo->prepare('
        INSERT INTO RepaymentSchedule (Repayment_ID, Loan_ID, Due_Date, Amount_Due, Loan_Status)
        VALUES (?, ?, ?, ?, ?)
    ');
    $stmt->execute(array($repaymentID, $loanID, $todayStr, $amountPaid, $repaymentStatus));

    $stmt = $pdo->prepare('INSERT INTO Transactions (Transaction_Type, Loan_ID, Transaction_Date) VALUES ("repayment", ?, ?)');
    $stmt->execute(array($loanID, $todayStr));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error recording repayment: ' . $e->getMessage()));
    exit;
}

// Check if loan is fully paid
try {
    $stmt      = $pdo->prepare('SELECT SUM(Amount_Due) AS totalPaid FROM RepaymentSchedule WHERE Loan_ID = ?');
    $stmt->execute(array($loanID));
    $result    = $stmt->fetch();
    $totalPaid = $result['totalPaid'] ?? 0;

    if ($totalPaid >= $loan['Amount']) {
        $stmt = $pdo->prepare('UPDATE Loan SET Loan_Status = "settled" WHERE Loan_ID = ?');
        $stmt->execute(array($loanID));
        $loanStatus = 'settled';
    } else {
        $loanStatus = 'disbursed';
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error updating loan status: ' . $e->getMessage()));
    exit;
}

//Update credit score
// Late repayment = decrease, on-time = increase
if ($isLate) {
    $newCreditScore = decreaseCredit($pdo, $loggedInUser['User_ID']);
    $creditMessage  = 'Your credit score decreased due to a late repayment.';
} else {
    $newCreditScore = increaseCredit($pdo, $loggedInUser['User_ID']);
    $creditMessage  = 'Your credit score increased due to a successful repayment.';
}

//Respond
http_response_code(200);
echo json_encode(array(
    'success'         => true,
    'message'         => 'Repayment recorded successfully!',
    'loanID'          => $loanID,
    'amountPaid'      => $amountPaid,
    'totalPaid'       => $totalPaid,
    'loanAmount'      => $loan['Amount'],
    'loanStatus'      => $loanStatus,
    'repaymentStatus' => $repaymentStatus,
    'newCreditScore'  => $newCreditScore,
    'creditMessage'   => $creditMessage,
));