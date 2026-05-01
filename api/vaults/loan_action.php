<?php
// api/vaults/loan_action.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['requestID']) || empty($data['action'])) {
    http_response_code(400);
    echo json_encode(["error" => "Request ID and Action (approve/reject) are required."]);
    exit();
}

$requestID = (int)$data['requestID'];
$action = $data['action'];

try {
    $conn = Database::getInstance();
    $conn->beginTransaction();

    // 1. Get request and vault details
    $stmt = $conn->prepare("
        SELECT lr.*, v.duration, v.id AS v_id 
        FROM loan_requests lr 
        JOIN vaults v ON lr.vault_id = v.id 
        WHERE lr.id = ? FOR UPDATE
    ");
    $stmt->execute([$requestID]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request || $request['status'] !== 'pending') {
        throw new Exception("Request not found or already processed.");
    }

    if ($action === 'approve') {
        // A. Update request status
        $stmt = $conn->prepare("UPDATE loan_requests SET status = 'approved' WHERE id = ?");
        $stmt->execute([$requestID]);

        // B. Calculate Due Date
        $dueDate = date('Y-m-d H:i:s', strtotime("+{$request['duration']} days"));

        // C. Create Active Contract
        $stmt = $conn->prepare("INSERT INTO active_contracts (vault_id, borrower_id, due_date, status) VALUES (?, ?, ?, 'active')");
        $stmt->execute([$request['v_id'], $request['borrower_id'], $dueDate]);

        // D. Log Transaction
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount) VALUES (?, 'loan_disbursement', ?)");
        $stmt->execute([$request['borrower_id'], $request['requested_amount']]);

        $message = "Loan approved and contract activated.";

    } else if ($action === 'reject') {
        // A. Update status
        $stmt = $conn->prepare("UPDATE loan_requests SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$requestID]);

        // B. REFUND: Put the money back into available_amount!
        $stmt = $conn->prepare("UPDATE vaults SET available_amount = available_amount + ? WHERE id = ?");
        $stmt->execute([$request['requested_amount'], $request['v_id']]);

        $message = "Loan rejected and funds returned to vault.";
    }

    $conn->commit();
    echo json_encode(["message" => $message]);

} catch(Exception $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>