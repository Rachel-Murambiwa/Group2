<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once 'db.php';
require_once 'auth.php';

//Get user details
$userInfo = array(
    'userID'      => $loggedInUser['User_ID'],
    'firstName'   => $loggedInUser['First_Name'],
    'lastName'    => $loggedInUser['Last_Name'],
    'email'       => $loggedInUser['Email'],
    'phoneNumber' => $loggedInUser['Phone_Number'],
    'bankName'    => $loggedInUser['BankName'],
    'codeName'    => $loggedInUser['Code_Name'],
    'creditScore' => $loggedInUser['Credit_Score'],
);

//Get user's loans
try {
    $stmt = $pdo->prepare('SELECT * FROM Loan WHERE Borrower_ID = ? ORDER BY Date_Requested DESC');
    $stmt->execute(array($loggedInUser['User_ID']));
    $loans = $stmt->fetchAll();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error fetching loans: ' . $e->getMessage()));
    exit;
}

//Get user's transactions
try {
    $stmt = $pdo->prepare('
        SELECT t.* FROM Transactions t
        INNER JOIN Loan l ON t.Loan_ID = l.Loan_ID
        WHERE l.Borrower_ID = ? OR l.Lender_ID = ?
        ORDER BY t.Transaction_Date DESC
    ');
    $stmt->execute(array($loggedInUser['User_ID'], $loggedInUser['User_ID']));
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error fetching transactions: ' . $e->getMessage()));
    exit;
}

//Get repayment schedules
try {
    $stmt = $pdo->prepare('
        SELECT r.* FROM RepaymentSchedule r
        INNER JOIN Loan l ON r.Loan_ID = l.Loan_ID
        WHERE l.Borrower_ID = ?
        ORDER BY r.Due_Date ASC
    ');
    $stmt->execute(array($loggedInUser['User_ID']));
    $repayments = $stmt->fetchAll();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error fetching repayments: ' . $e->getMessage()));
    exit;
}

//Respond
http_response_code(200);
echo json_encode(array(
    'success'      => true,
    'user'         => $userInfo,
    'loans'        => $loans,
    'transactions' => $transactions,
    'repayments'   => $repayments,
));