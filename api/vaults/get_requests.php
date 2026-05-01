<?php
// api/vaults/get_requests.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../db.php';

try {
    $conn = Database::getInstance();

    // Query to get pending requests with borrower and vault details
    $query = "
        SELECT 
            lr.id AS request_id,
            lr.requested_amount,
            lr.amount_to_repay,
            lr.status,
            lr.created_at,
            u.alias AS borrower_alias,
            v.alias AS vault_alias,
            v.interest,
            v.duration
        FROM loan_requests lr
        JOIN users u ON lr.borrower_id = u.id
        JOIN vaults v ON lr.vault_id = v.id
        WHERE lr.status = 'pending'
        ORDER BY lr.created_at ASC
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(["requests" => $requests]);

} catch(PDOException $e) {
    http_response_code(500);
    error_log("Admin Fetch Error: " . $e->getMessage());
    echo json_encode(["error" => "Failed to fetch loan requests."]);
}
?>