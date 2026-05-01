<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');
require_once 'auth.php';

//Read input
$body   = api_input();
$loanID = api_value($body, array('loanID', 'loanId', 'loan_id', 'id'), '');
$interestRate = api_value($body, array('interestRate', 'interest_rate', 'rate'), '');

if (empty($loanID)) {
    api_json(array('success' => false, 'message' => 'Loan ID is required.'), 422);
}

if (empty($interestRate) || $interestRate <= 0) {
    api_json(array('success' => false, 'message' => 'Interest rate is required and must be greater than zero.'), 422);
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

//Only admin-approved loans can be funded by lenders.
if ($loan['Loan_Status'] !== 'approved') {
    api_json(array('success' => false, 'message' => 'This loan must be approved by admin before it can be funded.'), 400);
}

//Lender cannot fund their own loan
if ($loan['Borrower_ID'] === $loggedInUser['User_ID']) {
    api_json(array('success' => false, 'message' => 'You cannot fund your own loan request.'), 400);
}

//Update loan and record transaction
try {
    $dateDisbursed = date('Y-m-d');

    $stmt = $pdo->prepare('
        UPDATE Loan SET Lender_ID = ?, Interest_Rate = ?, Loan_Status = "disbursed", Date_Disbursed = ?
        WHERE Loan_ID = ?
    ');
    $stmt->execute(array($loggedInUser['User_ID'], $interestRate, $dateDisbursed, $loanID));

    $stmt = $pdo->prepare('INSERT INTO Transactions (Transaction_Type, Loan_ID, Transaction_Date) VALUES ("loan release", ?, ?)');
    $stmt->execute(array($loanID, $dateDisbursed));
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error funding loan: ' . $e->getMessage()), 500);
}

//Respond
api_json(array(
    'success'       => true,
    'message'       => 'Loan funded successfully!',
    'loanID'        => $loanID,
    'amount'        => $loan['Amount'],
    'interestRate'  => $interestRate,
    'dateDisbursed' => $dateDisbursed,
    'loan'          => api_loan(array_merge($loan, array(
        'Lender_ID' => $loggedInUser['User_ID'],
        'Interest_Rate' => $interestRate,
        'Loan_Status' => 'disbursed',
        'Date_Disbursed' => $dateDisbursed,
    ))),
));
