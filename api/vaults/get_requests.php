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

    // FIXED QUERY: Join users twice to get both the borrower and the lender aliases
    $query = "
        SELECT 
            lr.id AS request_id,
            lr.requested_amount,
            lr.amount_to_repay,
            lr.status,
            lr.created_at,
            u_borrower.alias AS borrower_alias,
            u_lender.alias AS vault_alias,
            v.interest,
            v.duration
        FROM loan_requests lr
        JOIN users u_borrower ON lr.borrower_id = u_borrower.id
        JOIN vaults v ON lr.vault_id = v.id
        JOIN users u_lender ON v.user_id = u_lender.id
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
    // This logs the ACTUAL error to your PHP error log so you can see it
    error_log("SQL Error: " . $e->getMessage()); 
    echo json_encode(["error" => "Failed to fetch loan requests."]);
}
?>