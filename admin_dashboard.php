<?php
// Shows all users, vaults, loan requests and transactions for the admin
require_once 'db.php';
api_cors('GET');
api_require_method('GET');
require_once 'admin_auth.php'; // Blocks anyone who is not admin

//Get all users
try {
    $stmt  = $pdo->query('SELECT id, full_name, phone, email, alias, is_verified, created_at FROM users');
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching users: ' . $e->getMessage()), 500);
}

//Get all vaults
try {
    $stmt = $pdo->query('
        SELECT v.*, u.full_name as owner_name, u.alias as owner_alias 
        FROM vaults v 
        JOIN users u ON v.user_id = u.id 
        ORDER BY v.created_at DESC
    ');
    $vaults = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching vaults: ' . $e->getMessage()), 500);
}

//Get all loan requests
try {
    $stmt = $pdo->query('
        SELECT lr.*, v.amount, v.interest, v.duration, 
               u1.full_name as borrower_name, u1.alias as borrower_alias,
               u2.full_name as lender_name, u2.alias as lender_alias
        FROM loan_requests lr 
        JOIN vaults v ON lr.vault_id = v.id 
        JOIN users u1 ON lr.borrower_id = u1.id
        JOIN users u2 ON v.user_id = u2.id
        ORDER BY lr.created_at DESC
    ');
    $loanRequests = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching loan requests: ' . $e->getMessage()), 500);
}

//Get all active contracts
try {
    $stmt = $pdo->query('
        SELECT ac.*, v.amount, v.interest, v.duration,
               u1.full_name as borrower_name, u1.alias as borrower_alias,
               u2.full_name as lender_name, u2.alias as lender_alias
        FROM active_contracts ac 
        JOIN vaults v ON ac.vault_id = v.id 
        JOIN users u1 ON ac.borrower_id = u1.id
        JOIN users u2 ON v.user_id = u2.id
        ORDER BY ac.due_date ASC
    ');
    $activeContracts = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching active contracts: ' . $e->getMessage()), 500);
}

//Get all transactions
try {
    $stmt = $pdo->query('
        SELECT t.*, u.full_name as user_name, u.alias as user_alias
        FROM transactions t 
        JOIN users u ON t.user_id = u.id 
        ORDER BY t.created_at DESC
    ');
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching transactions: ' . $e->getMessage()), 500);
}

//Simple summary stats
$totalUsers           = count($users);
$verifiedUsers        = count(array_filter($users, fn($u) => $u['is_verified']));
$totalVaults          = count($vaults);
$availableVaults      = count(array_filter($vaults, fn($v) => $v['status'] === 'available'));
$activeVaults         = count(array_filter($vaults, fn($v) => $v['status'] === 'active'));
$totalLoanRequests    = count($loanRequests);
$pendingRequests      = count(array_filter($loanRequests, fn($lr) => $lr['status'] === 'pending'));
$approvedRequests     = count(array_filter($loanRequests, fn($lr) => $lr['status'] === 'approved'));
$totalActiveContracts = count($activeContracts);
$totalTransactions    = count($transactions);

//Respond
api_json(array(
    'success' => true,
    'summary' => array(
        'totalUsers'           => $totalUsers,
        'verifiedUsers'        => $verifiedUsers,
        'totalVaults'          => $totalVaults,
        'availableVaults'      => $availableVaults,
        'activeVaults'         => $activeVaults,
        'totalLoanRequests'    => $totalLoanRequests,
        'pendingRequests'      => $pendingRequests,
        'approvedRequests'     => $approvedRequests,
        'totalActiveContracts' => $totalActiveContracts,
        'totalTransactions'    => $totalTransactions,
    ),
    'users'           => $users,
    'vaults'          => $vaults,
    'loanRequests'    => $loanRequests,
    'activeContracts' => $activeContracts,
    'transactions'    => $transactions,
));
