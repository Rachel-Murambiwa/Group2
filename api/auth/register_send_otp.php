<?php

require_once __DIR__ . '/../../frontend_api.php';

api_cors('POST');
api_require_method('POST');

$body = api_input();
$fullName = trim(api_value($body, array('fullName', 'full_name', 'name'), ''));
$phone = trim(api_value($body, array('phone'), ''));
$alias = trim(api_value($body, array('alias'), ''));
$password = api_value($body, array('password'), '');

if ($fullName === '' || $phone === '' || $alias === '' || $password === '') {
    frontend_format_error('Incomplete data.', 400);
}

list($firstName, $lastName) = frontend_full_name_parts($fullName);
$otp = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$email = frontend_placeholder_email($phone);
$expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

try {
    $stmt = $pdo->prepare('SELECT * FROM Users WHERE Phone_Number = ? LIMIT 1');
    $stmt->execute(array($phone));
    $existingUser = $stmt->fetch();

    if ($existingUser && (bool) $existingUser['Is_Verified']) {
        frontend_format_error('This number is already verified. Please log in.', 409);
    }

    if ($existingUser) {
        $userID = $existingUser['User_ID'];
        $stmt = $pdo->prepare('
            UPDATE Users
            SET First_Name = ?, Last_Name = ?, Email = ?, User_Password = ?, Code_Name = ?
            WHERE User_ID = ?
        ');
        $stmt->execute(array($firstName, $lastName, $email, $hashedPassword, $alias, $userID));
    } else {
        $userID = frontend_user_id($pdo);
        $stmt = $pdo->prepare('
            INSERT INTO Users
                (User_ID, First_Name, Last_Name, Email, Phone_Number, User_Password, Code_Name, Credit_Score, Is_Verified)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, 5.0, FALSE)
        ');
        $stmt->execute(array($userID, $firstName, $lastName, $email, $phone, $hashedPassword, $alias));
    }

    $stmt = $pdo->prepare('DELETE FROM Verification WHERE User_ID = ?');
    $stmt->execute(array($userID));

    $stmt = $pdo->prepare('
        INSERT INTO Verification (Verification_ID, User_ID, Email, Verification_Code, Expiry_Date)
        VALUES (?, ?, ?, ?, ?)
    ');
    $stmt->execute(array(frontend_verification_id($pdo), $userID, $email, $otp, $expiresAt));
} catch (PDOException $e) {
    frontend_format_error('Failed to process registration.', 500);
}

api_json(array(
    'message' => 'OTP Sent',
    'debug_otp' => $otp,
));
