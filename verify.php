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
$body  = json_decode(file_get_contents('php://input'), true);
$email = trim($body['email'] ?? '');
$code  = trim($body['code']  ?? '');

if (empty($email) || empty($code)) {
    http_response_code(400);
    echo json_encode(array('success' => false, 'message' => 'Email and code are required.'));
    exit;
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
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error finding verification record: ' . $e->getMessage()));
    exit;
}

if (!$record) {
    http_response_code(404);
    echo json_encode(array('success' => false, 'message' => 'No verification record found for this email.'));
    exit;
}

//Check expiry
$now    = new DateTime();
$expiry = new DateTime($record['Expiry_Date']);

if ($now > $expiry) {
    http_response_code(410);
    echo json_encode(array('success' => false, 'message' => 'Verification code has expired. Please register again.'));
    exit;
}

//Check code matches
if ($code !== $record['Verification_Code']) {
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'Incorrect verification code.'));
    exit;
}

//Mark user as verified
try {
    $stmt = $pdo->prepare('UPDATE Users SET Is_Verified = TRUE WHERE Email = ?');
    $stmt->execute(array($email));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Error verifying user: ' . $e->getMessage()));
    exit;
}

http_response_code(200);
echo json_encode(array(
    'success' => true,
    'message' => 'Email verified successfully! You can now log in.',
));