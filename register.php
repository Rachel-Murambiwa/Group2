<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');

//Read input
$body        = api_input();
$fullName    = trim(api_value($body, array('fullName', 'full_name', 'name'), ''));
$firstName   = trim(api_value($body, array('firstName', 'first_name', 'firstname'), ''));
$lastName    = trim(api_value($body, array('lastName', 'last_name', 'lastname'), ''));
$email       = trim(api_value($body, array('email', 'Email'), ''));
$phoneNumber = trim(api_value($body, array('phoneNumber', 'phone_number', 'phone'), ''));
$password    = api_value($body, array('password', 'userPassword', 'user_password'), '');

// If fullName is provided, split it into first and last name
if (!empty($fullName) && (empty($firstName) || empty($lastName))) {
    $nameParts = explode(' ', $fullName, 2);
    $firstName = $nameParts[0] ?? '';
    $lastName = $nameParts[1] ?? '';
}

//Validate
$errors = array();
if (empty($firstName))   $errors[] = 'First name is required.';
if (empty($phoneNumber)) $errors[] = 'Phone number is required.';
if (empty($password))    $errors[] = 'Password is required.';
if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';

// Use phone as email if no email provided
if (empty($email)) {
    $email = $phoneNumber . '@charleedash.local';
}

if (!empty($errors)) {
    api_json(array('success' => false, 'message' => implode(' ', $errors), 'errors' => $errors), 422);
}

//Check phone not already taken
try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE phone = ?');
    $stmt->execute(array($phoneNumber));
    if ($stmt->fetch()) {
        api_json(array('success' => false, 'message' => 'Phone number already registered.'), 409);
    }
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error checking phone: ' . $e->getMessage()), 500);
}

//Generate unique alias
function generateAlias($pdo) {
    $words = array('CHALE', 'BOSS', 'STAR', 'ACE', 'PRO');
    do {
        $alias = $words[array_rand($words)] . rand(10, 99);
        $stmt = $pdo->prepare('SELECT id FROM users WHERE alias = ?');
        $stmt->execute(array($alias));
    } while ($stmt->fetch());
    return $alias;
}

$alias = generateAlias($pdo);
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

//Save user
try {
    $stmt = $pdo->prepare('
        INSERT INTO users (full_name, phone, email, password, alias, otp_code, is_verified, otp_created_at)
        VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
    ');
    $stmt->execute(array(trim($firstName . ' ' . $lastName), $phoneNumber, $email, $hashedPassword, $alias, $otpCode));
    $userId = $pdo->lastInsertId();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error saving user: ' . $e->getMessage()), 500);
}

//Respond
api_json(array(
    'success' => true,
    'message' => 'Registration successful! Check your phone for the verification code.',
    'userID'  => $userId,
    'alias' => $alias,
    'verificationCode' => $otpCode,
), 201);
