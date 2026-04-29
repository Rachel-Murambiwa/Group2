<?php

require_once __DIR__ . '/db.php';

date_default_timezone_set('Africa/Accra');

function frontend_full_name_parts($fullName) {
    $fullName = trim($fullName);
    $parts = preg_split('/\s+/', $fullName, 2);

    return array(
        $parts[0] ?? 'Student',
        $parts[1] ?? 'User',
    );
}

function frontend_user_id($pdo) {
    do {
        $id = 'USR' . str_pad((string) rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare('SELECT User_ID FROM Users WHERE User_ID = ?');
        $stmt->execute(array($id));
    } while ($stmt->fetch());

    return $id;
}

function frontend_verification_id($pdo) {
    do {
        $id = rand(100000, 999999);
        $stmt = $pdo->prepare('SELECT Verification_ID FROM Verification WHERE Verification_ID = ?');
        $stmt->execute(array($id));
    } while ($stmt->fetch());

    return $id;
}

function frontend_loan_id($pdo) {
    do {
        $id = rand(100000, 999999);
        $stmt = $pdo->prepare('SELECT Loan_ID FROM Loan WHERE Loan_ID = ?');
        $stmt->execute(array($id));
    } while ($stmt->fetch());

    return $id;
}

function frontend_placeholder_email($phone) {
    $safePhone = preg_replace('/[^0-9]/', '', $phone);
    return $safePhone . '@charleedash.local';
}

function frontend_user($user) {
    $alias = $user['Code_Name'] ?? '';

    return array(
        'id' => $user['User_ID'],
        'userID' => $user['User_ID'],
        'fullName' => trim(($user['First_Name'] ?? '') . ' ' . ($user['Last_Name'] ?? '')),
        'phone' => $user['Phone_Number'],
        'alias' => $alias,
        'is_verified' => (bool) ($user['Is_Verified'] ?? false),
    );
}

function frontend_format_error($message, $status = 400) {
    api_json(array('error' => $message), $status);
}
