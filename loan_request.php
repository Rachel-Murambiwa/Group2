<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');
require_once 'auth.php';

//Read input
$body    = api_input();
$vaultId = api_value($body, array('vaultId', 'vault_id', 'id'), '');

//Validate
if (empty($vaultId)) {
    api_json(array('success' => false, 'message' => 'Vault ID is required.'), 422);
}

//Check if vault exists and is available
try {
    $stmt = $pdo->prepare('SELECT * FROM vaults WHERE id = ? AND status = "available"');
    $stmt->execute(array($vaultId));
    $vault = $stmt->fetch();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error checking vault: ' . $e->getMessage()), 500);
}

if (!$vault) {
    api_json(array('success' => false, 'message' => 'Vault not found or not available.'), 404);
}

//Check if user is not the vault owner
if ($vault['user_id'] == $loggedInUser['id']) {
    api_json(array('success' => false, 'message' => 'You cannot request a loan from your own vault.'), 400);
}

//Check if user already has a pending request for this vault
try {
    $stmt = $pdo->prepare('SELECT id FROM loan_requests WHERE vault_id = ? AND borrower_id = ? AND status = "pending"');
    $stmt->execute(array($vaultId, $loggedInUser['id']));
    if ($stmt->fetch()) {
        api_json(array('success' => false, 'message' => 'You already have a pending request for this vault.'), 400);
    }
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error checking existing requests: ' . $e->getMessage()), 500);
}

//Create loan request
try {
    $stmt = $pdo->prepare('
        INSERT INTO loan_requests (vault_id, borrower_id, status, created_at)
        VALUES (?, ?, "pending", NOW())
    ');
    $stmt->execute(array($vaultId, $loggedInUser['id']));
    $requestId = $pdo->lastInsertId();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error creating loan request: ' . $e->getMessage()), 500);
}

//Respond
api_json(array(
    'success' => true,
    'message' => 'Loan request submitted successfully!',
    'requestId' => $requestId,
    'vaultId' => $vaultId,
    'amount' => $vault['amount'],
    'interest' => $vault['interest'],
    'duration' => $vault['duration'],
    'status' => 'pending',
), 201);
