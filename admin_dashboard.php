<?php
// Shows all users, loans and transactions for the admin
require_once 'db.php';
api_cors('GET');
api_require_method('GET');
require_once 'admin_auth.php'; // Blocks anyone who is not admin

//Get all users
try {
    $stmt  = $pdo->query('SELECT User_ID, First_Name, Last_Name, Email, Phone_Number, BankName, BankAccount, Code_Name, Credit_Score, Is_Verified FROM Users');
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching users: ' . $e->getMessage()), 500);
}

//Get all loans
try {
    $stmt  = $pdo->query('SELECT * FROM Loan ORDER BY Date_Requested DESC');
    $loans = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching loans: ' . $e->getMessage()), 500);
}

//Get all transactions
try {
    $stmt         = $pdo->query('SELECT * FROM Transactions ORDER BY Transaction_Date DESC');
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching transactions: ' . $e->getMessage()), 500);
}

//Get all repayments
try {
    $stmt       = $pdo->query('SELECT * FROM RepaymentSchedule ORDER BY Due_Date DESC');
    $repayments = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching repayments: ' . $e->getMessage()), 500);
}

//Simple summary stats
$totalUsers        = count($users);
$totalLoans        = count($loans);
$pendingLoans      = count(array_filter($loans, fn($l) => $l['Loan_Status'] === 'pending'));
$approvedLoans     = count(array_filter($loans, fn($l) => $l['Loan_Status'] === 'approved'));
$disbursedLoans    = count(array_filter($loans, fn($l) => $l['Loan_Status'] === 'disbursed'));
$settledLoans      = count(array_filter($loans, fn($l) => $l['Loan_Status'] === 'settled'));
$totalTransactions = count($transactions);

//Respond
api_json(array(
    'success' => true,
    'summary' => array(
        'totalUsers'        => $totalUsers,
        'totalLoans'        => $totalLoans,
        'pendingLoans'      => $pendingLoans,
        'approvedLoans'     => $approvedLoans,
        'disbursedLoans'    => $disbursedLoans,
        'settledLoans'      => $settledLoans,
        'totalTransactions' => $totalTransactions,
    ),
    'users'        => array_map('api_user', $users),
    'loans'        => api_loans($loans),
    'transactions' => api_transactions($transactions),
    'repayments'   => api_repayments($repayments),
));
