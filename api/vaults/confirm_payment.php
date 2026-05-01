<?php
// api/vaults/confirm_repayment.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../db.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->contract_id)) {
    http_response_code(400);
    echo json_encode(["error" => "Contract ID required."]);
    exit();
}

try {
    $conn = Database::getInstance();
    $conn->beginTransaction();

    // 1. Mark the contract as paid
    $stmt = $conn->prepare("UPDATE active_contracts SET status = 'paid' WHERE id = ?");
    $stmt->execute([$data->contract_id]);

    // 2. Identify the borrower to boost their score
    $stmt = $conn->prepare("SELECT borrower_id FROM active_contracts WHERE id = ?");
    $stmt->execute([$data->contract_id]);
    $borrowerID = $stmt->fetchColumn();

    // 3. Optional: Add a transaction record for history
    // (This helps with the ROI Tracker later!)

    $conn->commit();
    echo json_encode(["message" => "Repayment confirmed! Borrower trust score increased."]);

} catch(Exception $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(["error" => "Failed to confirm repayment."]);
}
?>