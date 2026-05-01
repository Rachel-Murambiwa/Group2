<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');

//Read input
$body        = api_input();
$firstName   = trim(api_value($body, array('firstName', 'first_name', 'firstname'), ''));
$lastName    = trim(api_value($body, array('lastName', 'last_name', 'lastname'), ''));
$email       = trim(api_value($body, array('email', 'Email'), ''));
$phoneNumber = trim(api_value($body, array('phoneNumber', 'phone_number', 'phone'), ''));
$bankName    = trim(api_value($body, array('bankName', 'bank_name', 'bank'), ''));
$bankAccount = trim(api_value($body, array('bankAccount', 'bank_account', 'accountNumber', 'account_number'), ''));
$password    = api_value($body, array('password', 'userPassword', 'user_password'), '');

//Validate
$errors = array();
if (empty($firstName))   $errors[] = 'First name is required.';
if (empty($lastName))    $errors[] = 'Last name is required.';
if (empty($email))       $errors[] = 'Email is required.';
if (empty($phoneNumber)) $errors[] = 'Phone number is required.';
if (empty($bankName))    $errors[] = 'Bank name is required.';
if (empty($bankAccount)) $errors[] = 'Bank account is required.';
if (empty($password))    $errors[] = 'Password is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
if (!str_ends_with(strtolower($email), '@ashesi.edu.gh')) $errors[] = 'Email must be an Ashesi email (@ashesi.edu.gh).';
if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';

if (!empty($errors)) {
    api_json(array('success' => false, 'message' => implode(' ', $errors), 'errors' => $errors), 422);
}

//Check email not already taken
try {
    $stmt = $pdo->prepare('SELECT User_ID FROM Users WHERE Email = ?');
    $stmt->execute(array($email));
    if ($stmt->fetch()) {
        api_json(array('success' => false, 'message' => 'Email already registered.'), 409);
    }
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error checking email: ' . $e->getMessage()), 500);
}

//Generate unique User_ID
function generateUserID($pdo) {
    do {
        $id   = 'USR' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare('SELECT User_ID FROM Users WHERE User_ID = ?');
        $stmt->execute(array($id));
    } while ($stmt->fetch());
    return $id;
}

//Generate unique CodeName
function generateCodeName($pdo) {
    $words = array('CHALE', 'BOSS', 'STAR', 'ACE', 'PRO');
    do {
        $code = $words[array_rand($words)] . rand(10, 99);
        $stmt = $pdo->prepare('SELECT User_ID FROM Users WHERE Code_Name = ?');
        $stmt->execute(array($code));
    } while ($stmt->fetch());
    return $code;
}

$userID         = generateUserID($pdo);
$codeName       = generateCodeName($pdo);
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

//Save user
try {
    $stmt = $pdo->prepare('
        INSERT INTO Users (User_ID, First_Name, Last_Name, Email, Phone_Number, User_Password, BankName, BankAccount, Code_Name, Credit_Score, Is_Verified)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 5.0, FALSE)
    ');
    $stmt->execute(array($userID, $firstName, $lastName, $email, $phoneNumber, $hashedPassword, $bankName, $bankAccount, $codeName));
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error saving user: ' . $e->getMessage()), 500);
}

//Save verification code
try {
    $verificationCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiryDate       = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    $verificationID   = rand(100000, 999999);

    $stmt = $pdo->prepare('
        INSERT INTO Verification (Verification_ID, User_ID, Email, Verification_Code, Expiry_Date)
        VALUES (?, ?, ?, ?, ?)
    ');
    $stmt->execute(array($verificationID, $userID, $email, $verificationCode, $expiryDate));
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error saving verification: ' . $e->getMessage()), 500);
}

//Respond
api_json(array(
    'success' => true,
    'message' => 'Registration successful! Check your email for the verification code.',
    'userID'  => $userID,
    'codeName' => $codeName,
    'verificationCode' => $verificationCode,
), 201);
