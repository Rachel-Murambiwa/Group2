<?php
// api/vaults/mark_as_paid.php
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
    
    // Update status to pending_confirmation
    $stmt = $conn->prepare("UPDATE active_contracts SET status = 'pending_confirmation' WHERE id = ?");
    $stmt->execute([$data->contract_id]);

    echo json_encode(["message" => "Contract marked as paid. Awaiting lender confirmation."]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to update status."]);
}
?>