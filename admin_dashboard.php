<?php
// Shows all users, loans and transactions for the admin
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
require_once 'admin_auth.php'; // Blocks anyone who is not admin

//Get all users
try {
    $stmt  = $pdo->query('SELECT User_ID, First_Name, Last_Name, Email, Phone_Number, BankName, Code_Name, Credit_Score, Is_Verified FROM Users');
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error fetching users: ' . $e->getMessage()));
    exit;
}

//Get all loans
try {
    $stmt  = $pdo->query('SELECT * FROM Loan ORDER BY Date_Requested DESC');
    $loans = $stmt->fetchAll();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error fetching loans: ' . $e->getMessage()));
    exit;
}

//Get all transactions
try {
    $stmt         = $pdo->query('SELECT * FROM Transactions ORDER BY Transaction_Date DESC');
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error fetching transactions: ' . $e->getMessage()));
    exit;
}

//Get all repayments
try {
    $stmt       = $pdo->query('SELECT * FROM RepaymentSchedule ORDER BY Due_Date DESC');
    $repayments = $stmt->fetchAll();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error fetching repayments: ' . $e->getMessage()));
    exit;
}

//Simple summary stats
$totalUsers        = count($users);
$totalLoans        = count($loans);
$pendingLoans      = count(array_filter($loans, fn($l) => $l['Loan_Status'] === 'pending'));
$disbursedLoans    = count(array_filter($loans, fn($l) => $l['Loan_Status'] === 'disbursed'));
$settledLoans      = count(array_filter($loans, fn($l) => $l['Loan_Status'] === 'settled'));
$totalTransactions = count($transactions);

//Respond
http_response_code(200);
echo json_encode(array(
    'success' => true,
    'summary' => array(
        'totalUsers'        => $totalUsers,
        'totalLoans'        => $totalLoans,
        'pendingLoans'      => $pendingLoans,
        'disbursedLoans'    => $disbursedLoans,
        'settledLoans'      => $settledLoans,
        'totalTransactions' => $totalTransactions,
    ),
    'users'        => $users,
    'loans'        => $loans,
    'transactions' => $transactions,
    'repayments'   => $repayments,
));