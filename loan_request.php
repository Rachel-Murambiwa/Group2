<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');
require_once 'auth.php';

//Read input
$body           = api_input();
$amount         = api_value($body, array('amount', 'loanAmount', 'loan_amount'), '');
$purpose        = api_value($body, array('purpose', 'loanPurpose', 'loan_purpose'), '');
$durationMonths = api_value($body, array('durationMonths', 'duration_months', 'duration', 'months'), '');

//Validate
$errors          = array();
$allowedPurposes = array('healthcare', 'transportation', 'recreation', 'charity', 'other');

if (empty($amount))         $errors[] = 'Amount is required.';
if (empty($purpose))        $errors[] = 'Purpose is required.';
if (empty($durationMonths)) $errors[] = 'Duration is required.';
if ($amount <= 0)           $errors[] = 'Amount must be greater than zero.';
if ($durationMonths <= 0)   $errors[] = 'Duration must be greater than zero.';
if (!in_array(strtolower($purpose), $allowedPurposes)) {
    $errors[] = 'Purpose must be one of: healthcare, transportation, recreation, charity, other.';
}
$purpose = strtolower($purpose);

if (!empty($errors)) {
    api_json(array('success' => false, 'message' => implode(' ', $errors), 'errors' => $errors), 422);
}

//Save loan request
try {
    $stmt   = $pdo->query('SELECT MAX(Loan_ID) AS maxID FROM Loan');
    $row    = $stmt->fetch();
    $loanID = ($row['maxID'] ?? 0) + 1;

    $stmt = $pdo->prepare('
        INSERT INTO Loan (Loan_ID, Borrower_ID, Amount, Purpose, Duration_Months, Interest_Rate, Loan_Status, Date_Requested)
        VALUES (?, ?, ?, ?, ?, 0, "pending", ?)
    ');
    $stmt->execute(array($loanID, $loggedInUser['User_ID'], $amount, $purpose, $durationMonths, date('Y-m-d')));
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error saving loan: ' . $e->getMessage()), 500);
}

//Respond
api_json(array(
    'success' => true,
    'message' => 'Loan request submitted successfully!',
    'loanID'  => $loanID,
    'amount'  => $amount,
    'purpose' => $purpose,
    'status'  => 'pending',
    'loan'    => api_loan(array(
        'Loan_ID' => $loanID,
        'Borrower_ID' => $loggedInUser['User_ID'],
        'Lender_ID' => null,
        'Amount' => $amount,
        'Duration_Months' => $durationMonths,
        'Interest_Rate' => 0,
        'Loan_Status' => 'pending',
        'Date_Requested' => date('Y-m-d'),
        'Date_Disbursed' => null,
        'Purpose' => $purpose,
    )),
), 201);
