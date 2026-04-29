<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');
require_once 'auth.php';
require_once 'credit_score.php';

//Read input
$body       = api_input();
$loanID     = api_value($body, array('loanID', 'loanId', 'loan_id', 'id'), '');
$amountPaid = api_value($body, array('amountPaid', 'amount_paid', 'amount', 'paymentAmount', 'payment_amount'), '');

//Validate
$errors = array();
if (empty($loanID))     $errors[] = 'Loan ID is required.';
if (empty($amountPaid)) $errors[] = 'Amount paid is required.';
if ($amountPaid <= 0)   $errors[] = 'Amount must be greater than zero.';

if (!empty($errors)) {
    api_json(array('success' => false, 'message' => implode(' ', $errors), 'errors' => $errors), 422);
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

// Make sure this is the borrower's loan
if ($loan['Borrower_ID'] !== $loggedInUser['User_ID']) {
    api_json(array('success' => false, 'message' => 'This is not your loan.'), 403);
}

// Check loan is active
if ($loan['Loan_Status'] !== 'disbursed') {
    api_json(array('success' => false, 'message' => 'Only disbursed loans can be repaid.'), 400);
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
    api_json(array('success' => false, 'message' => 'Error recording repayment: ' . $e->getMessage()), 500);
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
    api_json(array('success' => false, 'message' => 'Error updating loan status: ' . $e->getMessage()), 500);
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
api_json(array(
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
    'repayment'       => api_repayment(array(
        'Repayment_ID' => $repaymentID,
        'Loan_ID' => $loanID,
        'Due_Date' => $todayStr,
        'Amount_Due' => $amountPaid,
        'Loan_Status' => $repaymentStatus,
    )),
));
