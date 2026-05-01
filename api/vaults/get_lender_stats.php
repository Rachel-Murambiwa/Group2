<?php
// api/vaults/get_lender_stats.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../db.php';

$userId = isset($_GET['userID']) ? (int)$_GET['userID'] : 0;

try {
    $conn = Database::getInstance();

    // 1. Total Deployed (Sum of principal in all non-cancelled vaults)
    $stmt = $conn->prepare("SELECT SUM(amount) FROM vaults WHERE user_id = ? AND status != 'cancelled'");
    $stmt->execute([$userId]);
    $totalDeployed = $stmt->fetchColumn() ?: 0;

    // 2. Realized Profit (Interest from PAID contracts only)
    $stmt = $conn->prepare("
        SELECT SUM(lr.amount_to_repay - v.amount) 
        FROM active_contracts ac
        JOIN vaults v ON ac.vault_id = v.id
        JOIN loan_requests lr ON ac.borrower_id = lr.borrower_id AND ac.vault_id = lr.vault_id
        WHERE v.user_id = ? AND ac.status = 'paid' AND lr.status = 'approved'
    ");
    $stmt->execute([$userId]);
    $realizedProfit = $stmt->fetchColumn() ?: 0;

    // 3. Active Risk (Principal currently out in 'active', 'overdue', or 'pending_confirmation' status)
    $stmt = $conn->prepare("
        SELECT SUM(v.amount) 
        FROM active_contracts ac
        JOIN vaults v ON ac.vault_id = v.id
        WHERE v.user_id = ? AND ac.status IN ('active', 'overdue', 'pending_confirmation')
    ");
    $stmt->execute([$userId]);
    $activeRisk = $stmt->fetchColumn() ?: 0;

    // 4. Get counts for status badges
    $stmt = $conn->prepare("SELECT COUNT(*) FROM active_contracts ac JOIN vaults v ON ac.vault_id = v.id WHERE v.user_id = ? AND ac.status = 'paid'");
    $stmt->execute([$userId]);
    $paidCount = $stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM active_contracts ac JOIN vaults v ON ac.vault_id = v.id WHERE v.user_id = ? AND ac.status IN ('active', 'overdue')");
    $stmt->execute([$userId]);
    $activeCount = $stmt->fetchColumn();

    // 5. Fetch all contracts for the list
    $stmt = $conn->prepare("
        SELECT ac.id, v.amount, v.interest, ac.status, ac.due_date 
        FROM active_contracts ac
        JOIN vaults v ON ac.vault_id = v.id
        WHERE v.user_id = ?
        ORDER BY ac.id DESC
    ");
    $stmt->execute([$userId]);
    $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "stats" => [
            "total_deployed" => $totalDeployed,
            "realized_profit" => $realizedProfit,
            "active_risk" => $activeRisk,
            "active_count" => $activeCount,
            "paid_count" => $paidCount
        ],
        "contracts" => $contracts
    ]);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>