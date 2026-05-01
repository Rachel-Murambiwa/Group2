<?php
// api/vaults/request_funds.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../db.php';

// Read JSON payload
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['vaultID']) || empty($data['borrowerID'])) {
    http_response_code(400);
    echo json_encode(["error" => "Vault ID and Borrower ID are required."]);
    exit();
}

$vaultID = filter_var($data['vaultID'], FILTER_VALIDATE_INT);
$borrowerID = filter_var($data['borrowerID'], FILTER_VALIDATE_INT);

try {
    $conn = Database::getInstance();

    // 1. Check if the vault exists and is still available
    $stmt = $conn->prepare("SELECT status, user_id FROM vaults WHERE id = ?");
    $stmt->execute([$vaultID]);
    $vault = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vault || $vault['status'] !== 'available') {
        http_response_code(400);
        echo json_encode(["error" => "This vault is no longer available."]);
        exit();
    }

    // 2. Prevent a user from requesting their own funds
    if ($vault['user_id'] == $borrowerID) {
         http_response_code(400);
         echo json_encode(["error" => "You cannot request funds from your own deployed vault."]);
         exit();
    }

    // 3. Check if this borrower already has a pending request for this specific vault
    $stmt = $conn->prepare("SELECT id FROM loan_requests WHERE vault_id = ? AND borrower_id = ? AND status = 'pending'");
    $stmt->execute([$vaultID, $borrowerID]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(["error" => "You already have a pending request for this vault. Please wait for admin approval."]);
        exit();
    }

    // 4. Insert the request into the loan_requests table
    $stmt = $conn->prepare("INSERT INTO loan_requests (vault_id, borrower_id, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$vaultID, $borrowerID]);

    http_response_code(201);
    echo json_encode(["message" => "Loan request submitted successfully! An admin will review it shortly."]);

} catch(PDOException $e) {
    http_response_code(500);
    error_log("DB Error in request_funds.php: " . $e->getMessage());
    echo json_encode(["error" => "Failed to submit loan request due to a server error."]);
}
?>