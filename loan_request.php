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

//Read input
$body           = json_decode(file_get_contents('php://input'), true);
$amount         = $body['amount']         ?? '';
$purpose        = $body['purpose']        ?? '';
$durationMonths = $body['durationMonths'] ?? '';
$interestRate   = $body['interestRate']   ?? '';

//Validate
$errors          = array();
$allowedPurposes = array('healthcare', 'transportation', 'recreation', 'charity', 'other');

if (empty($amount))         $errors[] = 'Amount is required.';
if (empty($purpose))        $errors[] = 'Purpose is required.';
if (empty($durationMonths)) $errors[] = 'Duration is required.';
if (empty($interestRate))   $errors[] = 'Interest rate is required.';
if ($amount <= 0)           $errors[] = 'Amount must be greater than zero.';
if ($durationMonths <= 0)   $errors[] = 'Duration must be greater than zero.';
if ($interestRate <= 0)     $errors[] = 'Interest rate must be greater than zero.';
if (!in_array(strtolower($purpose), $allowedPurposes)) {
    $errors[] = 'Purpose must be one of: healthcare, transportation, recreation, charity, other.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(array('success' => false, 'errors' => $errors));
    exit;
}

//Save loan request
try {
    $stmt   = $pdo->query('SELECT MAX(Loan_ID) AS maxID FROM Loan');
    $row    = $stmt->fetch();
    $loanID = ($row['maxID'] ?? 0) + 1;

    $stmt = $pdo->prepare('
        INSERT INTO Loan (Loan_ID, Borrower_ID, Amount, Purpose, Duration_Months, Interest_Rate, Loan_Status, Date_Requested)
        VALUES (?, ?, ?, ?, ?, ?, "pending", ?)
    ');
    $stmt->execute(array($loanID, $loggedInUser['User_ID'], $amount, $purpose, $durationMonths, $interestRate, date('Y-m-d')));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error saving loan: ' . $e->getMessage()));
    exit;
}

//Respond
http_response_code(201);
echo json_encode(array(
    'success' => true,
    'message' => 'Loan request submitted successfully!',
    'loanID'  => $loanID,
    'amount'  => $amount,
    'purpose' => $purpose,
    'status'  => 'pending',
));