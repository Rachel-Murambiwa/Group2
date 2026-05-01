<?php

require_once 'db.php';
api_cors('GET');
api_require_method('GET');
require_once 'auth.php';

//Get user details
$userInfo = api_user($loggedInUser);

//Get user's loans
try {
    $stmt = $pdo->prepare('SELECT * FROM Loan WHERE Borrower_ID = ? ORDER BY Date_Requested DESC');
    $stmt->execute(array($loggedInUser['User_ID']));
    $loans = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching loans: ' . $e->getMessage()), 500);
}

//Get loans funded by this user
try {
    $stmt = $pdo->prepare('SELECT * FROM Loan WHERE Lender_ID = ? ORDER BY Date_Disbursed DESC');
    $stmt->execute(array($loggedInUser['User_ID']));
    $lentLoans = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching funded loans: ' . $e->getMessage()), 500);
}

//Get open loans this user can fund
try {
    $stmt = $pdo->prepare('
        SELECT * FROM Loan
        WHERE Loan_Status = "approved" AND Borrower_ID <> ?
        ORDER BY Date_Requested DESC
    ');
    $stmt->execute(array($loggedInUser['User_ID']));
    $availableLoans = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching available loans: ' . $e->getMessage()), 500);
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
    api_json(array('success' => false, 'message' => 'Error fetching transactions: ' . $e->getMessage()), 500);
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
    api_json(array('success' => false, 'message' => 'Error fetching repayments: ' . $e->getMessage()), 500);
}

//Respond
api_json(array(
    'success'        => true,
    'user'           => $userInfo,
    'loans'          => api_loans($loans),
    'borrowedLoans'  => api_loans($loans),
    'lentLoans'      => api_loans($lentLoans),
    'availableLoans' => api_loans($availableLoans),
    'transactions'   => api_transactions($transactions),
    'repayments'     => api_repayments($repayments),
));
