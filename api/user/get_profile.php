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
    // Vaults Funded
    $stmt = $conn->prepare("SELECT COUNT(*) FROM vaults WHERE user_id = ?");
    $stmt->execute([$userID]);
    $vaultsFunded = $stmt->fetchColumn();

    // Total Impact (Total amount this user has borrowed successfully)
    $stmt = $conn->prepare("SELECT SUM(requested_amount) FROM loan_requests WHERE borrower_id = ? AND status = 'approved'");
    $stmt->execute([$userID]);
    $totalImpact = $stmt->fetchColumn() ?: 0;

    // Trust Score Calculation (Base 500 + 50 per vault funded + 10 per successful borrow)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM loan_requests WHERE borrower_id = ? AND status = 'approved'");
    $stmt->execute([$userID]);
    $approvedLoans = $stmt->fetchColumn();
    $trustScore = 500 + ($vaultsFunded * 50) + ($approvedLoans * 10);

    // 2. FETCH ACTIVE COMMS (Where User is the BORROWER)
    // We need the Lender's phone number!
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
    // We need the Borrower's phone number!
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

    http_response_code(200);
    echo json_encode([
        "stats" => [
            "trustScore" => $trustScore,
            "vaultsFunded" => $vaultsFunded,
            "totalImpact" => $totalImpact,
            "repaymentRate" => "100%" // Hardcoded until we build repayment logic
        ],
        "comms" => [
            "borrowed" => $borrowedContracts,
            "lent" => $lentContracts
        ]
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to load profile data."]);
}
?>