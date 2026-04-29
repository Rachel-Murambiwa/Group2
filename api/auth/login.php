<?php

require_once __DIR__ . '/../../frontend_api.php';

api_cors('POST');
api_require_method('POST');

$body = api_input();
$phone = trim(api_value($body, array('phone'), ''));
$password = api_value($body, array('password'), '');

if ($phone === '' || $password === '') {
    frontend_format_error('Incomplete data.', 400);
}

try {
    $stmt = $pdo->prepare('SELECT * FROM Users WHERE Phone_Number = ? LIMIT 1');
    $stmt->execute(array($phone));
    $user = $stmt->fetch();
} catch (PDOException $e) {
    frontend_format_error('Database error while finding account.', 500);
}

if (!$user) {
    frontend_format_error('Account not found.', 404);
}

if (!(bool) $user['Is_Verified']) {
    frontend_format_error('Account not verified. Please register again to get a new OTP.', 403);
}

if (!password_verify($password, $user['User_Password'])) {
    frontend_format_error('Incorrect password.', 401);
}

api_json(array(
    'message' => 'Login successful',
    'user' => frontend_user($user),
));
