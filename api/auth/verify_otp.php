<?php

require_once __DIR__ . '/../../frontend_api.php';

api_cors('POST');
api_require_method('POST');

$body = api_input();
$phone = trim(api_value($body, array('phone'), ''));
$otp = trim(api_value($body, array('otp'), ''));

if ($phone === '' || $otp === '') {
    frontend_format_error('Incomplete data.', 400);
}

try {
    $stmt = $pdo->prepare('
        SELECT u.User_ID, v.Verification_Code, v.Expiry_Date
        FROM Users u
        JOIN Verification v ON v.User_ID = u.User_ID
        WHERE u.Phone_Number = ?
        ORDER BY v.Expiry_Date DESC
        LIMIT 1
    ');
    $stmt->execute(array($phone));
    $record = $stmt->fetch();
} catch (PDOException $e) {
    frontend_format_error('DB Connection Failed', 500);
}

if (!$record) {
    frontend_format_error('User not found.', 404);
}

if (new DateTime() > new DateTime($record['Expiry_Date'])) {
    frontend_format_error('OTP has expired. Please request a new one.', 401);
}

if ($record['Verification_Code'] !== $otp) {
    frontend_format_error('Invalid OTP code.', 401);
}

try {
    $stmt = $pdo->prepare('UPDATE Users SET Is_Verified = TRUE WHERE User_ID = ?');
    $stmt->execute(array($record['User_ID']));
} catch (PDOException $e) {
    frontend_format_error('Error verifying account.', 500);
}

api_json(array('message' => 'Account verified!'));
