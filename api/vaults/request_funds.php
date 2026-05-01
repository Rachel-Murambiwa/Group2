<?php
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

if (empty($data['vaultID']) || empty($data['borrowerID']) || empty($data['requestedAmount'])) {
    http_response_code(400);
    echo json_encode(["error" => "Vault ID, Borrower ID, and Amount are required."]);
    exit();
}

$vaultID = filter_var($data['vaultID'], FILTER_VALIDATE_INT);
$borrowerID = filter_var($data['borrowerID'], FILTER_VALIDATE_INT);
$requestedAmount = filter_var($data['requestedAmount'], FILTER_VALIDATE_FLOAT);

try {
    $conn = Database::getInstance();

    // 1. Fetch available_amount
    $stmt = $conn->prepare("SELECT amount, available_amount, interest, status, user_id FROM vaults WHERE id = ?");
    $stmt->execute([$vaultID]);
    $vault = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vault || $vault['status'] !== 'available') {
        http_response_code(400);
        echo json_encode(["error" => "This vault is no longer available."]);
        exit();
    }

    if ($vault['user_id'] == $borrowerID) {
         http_response_code(400);
         echo json_encode(["error" => "You cannot request funds from your own deployed vault."]);
         exit();
    }

    // 2. Validate against available_amount, not the total amount!
    if ($requestedAmount <= 0 || $requestedAmount > $vault['available_amount']) {
        http_response_code(400);
        echo json_encode(["error" => "Amount must be between 0.01 and " . $vault['available_amount']]);
        exit();
    }

    // Begin Transaction
    $conn->beginTransaction();

    try {
        $amountToRepay = $requestedAmount + ($requestedAmount * ($vault['interest'] / 100));

        // Insert the request
        $stmt = $conn->prepare("INSERT INTO loan_requests (vault_id, borrower_id, requested_amount, amount_to_repay, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$vaultID, $borrowerID, $requestedAmount, $amountToRepay]);

        // IMMEDIATELY deduct the requested amount from the vault
        $stmt = $conn->prepare("UPDATE vaults SET available_amount = available_amount - ? WHERE id = ?");
        $stmt->execute([$requestedAmount, $vaultID]);

        $conn->commit();

        http_response_code(201);
        $formattedRepayment = number_format($amountToRepay, 2);
        echo json_encode(["message" => "Request submitted! You will repay GHS {$formattedRepayment}."]);

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }

} catch(PDOException $e) {
    http_response_code(500);
    error_log("DB Error in request_funds.php: " . $e->getMessage());
    echo json_encode(["error" => "Failed to submit loan request."]);
}
?>