<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');

//Read input
$body = api_input();
$phone = trim(api_value($body, array('phone', 'phoneNumber', 'phone_number'), ''));
$otpCode = trim(api_value($body, array('otp', 'otpCode', 'otp_code', 'code'), ''));

//Validate
if (empty($phone) || empty($otpCode)) {
    api_json(array('success' => false, 'message' => 'Phone number and OTP code are required.'), 422);
}

//Find user
try {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE phone = ?');
    $stmt->execute(array($phone));
    $user = $stmt->fetch();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error finding user: ' . $e->getMessage()), 500);
}

if (!$user) {
    api_json(array('success' => false, 'message' => 'User not found.'), 404);
}

if ($user['is_verified']) {
    api_json(array('success' => false, 'message' => 'User already verified.'), 400);
}

//Check OTP
if ($user['otp_code'] !== $otpCode) {
    api_json(array('success' => false, 'message' => 'Invalid OTP code.'), 400);
}

//Check OTP expiry (15 minutes)
if ($user['otp_created_at'] && strtotime($user['otp_created_at']) < strtotime('-15 minutes')) {
    api_json(array('success' => false, 'message' => 'OTP code has expired.'), 400);
}

//Verify user
try {
    $stmt = $pdo->prepare('UPDATE users SET is_verified = 1, otp_code = NULL, otp_created_at = NULL WHERE id = ?');
    $stmt->execute(array($user['id']));
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error verifying user: ' . $e->getMessage()), 500);
}

//Respond
api_json(array(
    'success' => true,
    'message' => 'Phone number verified successfully!',
    'userID' => $user['id'],
));
