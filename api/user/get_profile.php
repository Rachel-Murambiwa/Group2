<?php
// api/user/get_profile.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../db.php';

if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "User ID required."]);
    exit();
}

$userID = (int)$_GET['user_id'];

try {
    $conn = Database::getInstance();

    // 1. CALCULATE DYNAMIC STATS
    $stmt = $conn->prepare("SELECT COUNT(*) FROM vaults WHERE user_id = ?");
    $stmt->execute([$userID]);
    $vaultsFunded = $stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT SUM(requested_amount) FROM loan_requests WHERE borrower_id = ? AND status = 'approved'");
    $stmt->execute([$userID]);
    $totalImpact = $stmt->fetchColumn() ?: 0;

    $stmt = $conn->prepare("SELECT COUNT(*) FROM loan_requests WHERE borrower_id = ? AND status = 'approved'");
    $stmt->execute([$userID]);
    $approvedLoans = $stmt->fetchColumn();
    $trustScore = 500 + ($vaultsFunded * 50) + ($approvedLoans * 10);

    // 2. FETCH ACTIVE COMMS (Where User is the BORROWER)
    $stmt = $conn->prepare("
        SELECT ac.id, lr.amount_to_repay, ac.due_date, u_lender.alias AS counterparty_alias, u_lender.phone AS counterparty_phone
        FROM active_contracts ac
        JOIN vaults v ON ac.vault_id = v.id
        JOIN users u_lender ON v.user_id = u_lender.id
        JOIN loan_requests lr ON ac.borrower_id = lr.borrower_id AND ac.vault_id = lr.vault_id AND lr.status = 'approved'
        WHERE ac.borrower_id = ? AND ac.status IN ('active', 'overdue')
    ");
    $stmt->execute([$userID]);
    $borrowedContracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. FETCH ACTIVE COMMS (Where User is the LENDER)
    $stmt = $conn->prepare("
        SELECT ac.id, lr.amount_to_repay, ac.due_date, u_borrower.alias AS counterparty_alias, u_borrower.phone AS counterparty_phone
        FROM active_contracts ac
        JOIN vaults v ON ac.vault_id = v.id
        JOIN users u_borrower ON ac.borrower_id = u_borrower.id
        JOIN loan_requests lr ON ac.borrower_id = lr.borrower_id AND ac.vault_id = lr.vault_id AND lr.status = 'approved'
        WHERE v.user_id = ? AND ac.status IN ('active', 'overdue')
    ");
    $stmt->execute([$userID]);
    $lentContracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. NEW: FETCH PENDING REQUESTS (Where User is the LENDER and someone wants their money)
    $stmt = $conn->prepare("
        SELECT lr.id, lr.requested_amount, u_borrower.alias AS counterparty_alias, u_borrower.phone AS counterparty_phone
        FROM loan_requests lr
        JOIN vaults v ON lr.vault_id = v.id
        JOIN users u_borrower ON lr.borrower_id = u_borrower.id
        WHERE v.user_id = ? AND lr.status = 'pending'
    ");
    $stmt->execute([$userID]);
    $pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "stats" => [
            "trustScore" => $trustScore,
            "vaultsFunded" => $vaultsFunded,
            "totalImpact" => $totalImpact,
            "repaymentRate" => "100%" 
        ],
        "comms" => [
            "borrowed" => $borrowedContracts,
            "lent" => $lentContracts,
            "pending" => $pendingRequests // Send the pending requests to React!
        ]
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to load profile data."]);
}
?>