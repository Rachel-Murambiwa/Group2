<?php
// api/vaults/get_lender_stats.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../db.php';

if (!isset($_GET['userID'])) {
    http_response_code(400);
    echo json_encode(["error" => "userID is required."]);
    exit();
}

$userID = filter_var($_GET['userID'], FILTER_VALIDATE_INT);

try {
    $conn = Database::getInstance();

    // 1. Calculate the high-level stats for the top cards
    $statsQuery = "
        SELECT 
            COALESCE(SUM(amount), 0) AS total_deployed,
            COALESCE(SUM(amount * (interest / 100)), 0) AS projected_returns,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_count,
            SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) AS paid_count
        FROM vaults 
        WHERE user_id = ?
    ";
    $stmt = $conn->prepare($statsQuery);
    $stmt->execute([$userID]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // If counts come back null (new user), ensure they are 0
    $stats['active_count'] = $stats['active_count'] ?? 0;
    $stats['paid_count'] = $stats['paid_count'] ?? 0;

    // 2. Fetch the list of contracts for the bottom right list
    $listQuery = "
        SELECT v.id, v.amount, v.interest, v.duration, v.status, ac.due_date 
        FROM vaults v
        LEFT JOIN active_contracts ac ON v.id = ac.vault_id
        WHERE v.user_id = ?
        ORDER BY v.created_at DESC
    ";
    $stmt = $conn->prepare($listQuery);
    $stmt->execute([$userID]);
    $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "stats" => $stats,
        "contracts" => $contracts
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    error_log("DB Error in get_lender_stats.php: " . $e->getMessage());
    echo json_encode(["error" => "Failed to fetch portfolio data."]);
}
?>