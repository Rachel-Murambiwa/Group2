<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');
require_once 'auth.php';

//Read input
$body     = api_input();
$amount   = api_value($body, array('amount', 'loanAmount', 'loan_amount'), '');
$interest = api_value($body, array('interest', 'interestRate', 'interest_rate'), '');
$duration = api_value($body, array('duration', 'durationMonths', 'duration_months'), '');

//Validate
$errors = array();
if (empty($amount))   $errors[] = 'Amount is required.';
if (empty($interest)) $errors[] = 'Interest rate is required.';
if (empty($duration)) $errors[] = 'Duration is required.';
if ($amount <= 0)     $errors[] = 'Amount must be greater than zero.';
if ($interest <= 0)   $errors[] = 'Interest rate must be greater than zero.';
if ($duration <= 0)   $errors[] = 'Duration must be greater than zero.';

if (!empty($errors)) {
    api_json(array('success' => false, 'message' => implode(' ', $errors), 'errors' => $errors), 422);
}

//Create vault (lending offer)
try {
    $stmt = $pdo->prepare('
        INSERT INTO vaults (user_id, amount, interest, duration, status, created_at)
        VALUES (?, ?, ?, ?, "available", NOW())
    ');
    $stmt->execute(array($loggedInUser['id'], $amount, $interest, $duration));
    $vaultId = $pdo->lastInsertId();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error creating vault: ' . $e->getMessage()), 500);
}

//Respond
api_json(array(
    'success' => true,
    'message' => 'Vault created successfully! Users can now request loans from your vault.',
    'vaultId' => $vaultId,
    'amount'  => $amount,
    'interest' => $interest,
    'duration' => $duration,
    'status'  => 'available',
), 201);
