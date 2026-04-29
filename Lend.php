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
$body   = json_decode(file_get_contents('php://input'), true);
$loanID = $body['loanID'] ?? '';

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

//Check loan is still pending
if ($loan['Loan_Status'] !== 'pending') {
    http_response_code(400);
    echo json_encode(array('success' => false, 'message' => 'This loan is no longer available for funding.'));
    exit;
}

//Lender cannot fund their own loan
if ($loan['Borrower_ID'] === $loggedInUser['User_ID']) {
    http_response_code(400);
    echo json_encode(array('success' => false, 'message' => 'You cannot fund your own loan request.'));
    exit;
}

//Update loan and record transaction
try {
    $dateDisbursed = date('Y-m-d');

    $stmt = $pdo->prepare('
        UPDATE Loan SET Lender_ID = ?, Loan_Status = "disbursed", Date_Disbursed = ?
        WHERE Loan_ID = ?
    ');
    $stmt->execute(array($loggedInUser['User_ID'], $dateDisbursed, $loanID));

    $stmt = $pdo->prepare('INSERT INTO Transactions (Transaction_Type, Loan_ID, Transaction_Date) VALUES ("loan release", ?, ?)');
    $stmt->execute(array($loanID, $dateDisbursed));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error funding loan: ' . $e->getMessage()));
    exit;
}

//Respond
http_response_code(200);
echo json_encode(array(
    'success'       => true,
    'message'       => 'Loan funded successfully!',
    'loanID'        => $loanID,
    'amount'        => $loan['Amount'],
    'dateDisbursed' => $dateDisbursed,
));