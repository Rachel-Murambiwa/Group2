<?php

require_once 'db.php';
api_cors('POST');
api_require_method('POST');

//Read input
$body  = api_input();
$email = trim(api_value($body, array('email', 'Email'), ''));
$code  = trim(api_value($body, array('code', 'verificationCode', 'verification_code'), ''));

if (empty($email) || empty($code)) {
    api_json(array('success' => false, 'message' => 'Email and code are required.'), 400);
}

//Find verification record
try {
    $stmt = $pdo->prepare('
        SELECT Verification_Code, Expiry_Date
        FROM Verification
        WHERE Email = ?
        ORDER BY Expiry_Date DESC
        LIMIT 1
    ');
    $stmt->execute(array($email));
    $record = $stmt->fetch();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error finding verification record: ' . $e->getMessage()), 500);
}

if (!$record) {
    api_json(array('success' => false, 'message' => 'No verification record found for this email.'), 404);
}

//Check expiry
$now    = new DateTime();
$expiry = new DateTime($record['Expiry_Date']);

if ($now > $expiry) {
    api_json(array('success' => false, 'message' => 'Verification code has expired. Please register again.'), 410);
}

//Check code matches
if ($code !== $record['Verification_Code']) {
    api_json(array('success' => false, 'message' => 'Incorrect verification code.'), 401);
}

//Mark user as verified
try {
    $stmt = $pdo->prepare('UPDATE Users SET Is_Verified = TRUE WHERE Email = ?');
    $stmt->execute(array($email));
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error verifying user: ' . $e->getMessage()), 500);
}

api_json(array(
    'success' => true,
    'message' => 'Email verified successfully! You can now log in.',
));
