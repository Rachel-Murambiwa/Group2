<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed.'));
    exit;
}

require_once 'db.php';

//Read input
$body        = json_decode(file_get_contents('php://input'), true);
$firstName   = trim($body['firstName']   ?? '');
$lastName    = trim($body['lastName']    ?? '');
$email       = trim($body['email']       ?? '');
$phoneNumber = trim($body['phoneNumber'] ?? '');
$bankName    = trim($body['bankName']    ?? '');
$bankAccount = trim($body['bankAccount'] ?? '');
$password    = $body['password']         ?? '';

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
    http_response_code(422);
    echo json_encode(array('success' => false, 'errors' => $errors));
    exit;
}

//Check email not already taken
try {
    $stmt = $pdo->prepare('SELECT User_ID FROM Users WHERE Email = ?');
    $stmt->execute(array($email));
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(array('success' => false, 'message' => 'Email already registered.'));
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error checking email: ' . $e->getMessage()));
    exit;
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
        INSERT INTO Users (User_ID, First_Name, Last_Name, Email, Phone_Number, User_Password, BankName, BankAccount, Code_Name, Is_Verified)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, FALSE)
    ');
    $stmt->execute(array($userID, $firstName, $lastName, $email, $phoneNumber, $hashedPassword, $bankName, $bankAccount, $codeName));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error saving user: ' . $e->getMessage()));
    exit;
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
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error saving verification: ' . $e->getMessage()));
    exit;
}

//Respond
http_response_code(201);
echo json_encode(array(
    'success' => true,
    'message' => 'Registration successful! Check your email for the verification code.',
    'userID'  => $userID,
));