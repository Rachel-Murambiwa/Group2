<?php
// api/vaults/get_active_loans.php
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

    // FIXED QUERY: Fully compliant with MySQL Strict Mode rules
    $query = "
        SELECT 
            ac.id AS contract_id,
            ac.due_date,
            ac.status,
            u.alias AS borrower_alias,
            MAX(lr.amount_to_repay) AS amount_to_repay,
            DATEDIFF(ac.due_date, NOW()) AS days_left
        FROM active_contracts ac
        JOIN users u ON ac.borrower_id = u.id
        JOIN loan_requests lr ON lr.vault_id = ac.vault_id 
            AND lr.borrower_id = ac.borrower_id 
            AND lr.status = 'approved'
        WHERE ac.status IN ('active', 'overdue')
        GROUP BY ac.id, ac.due_date, ac.status, u.alias
        ORDER BY ac.due_date ASC
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($loans as &$loan) {
        $loan['is_overdue'] = ((int)$loan['days_left'] < 0);
    }

    http_response_code(200);
    echo json_encode(["loans" => $loans]);

} catch(PDOException $e) {
    http_response_code(500);
    // Modified to echo the actual error so you can see it if it ever breaks again
    echo json_encode(["error" => "SQL Error: " . $e->getMessage()]);
}
?>