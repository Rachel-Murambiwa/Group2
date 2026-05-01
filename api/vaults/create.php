<?php

require_once __DIR__ . '/../../frontend_api.php';

api_cors('POST');
api_require_method('POST');

$body = api_input();
$userID = trim(api_value($body, array('userID', 'userId', 'id'), ''));
$amount = (float) api_value($body, array('amount'), 0);
$interest = (float) api_value($body, array('interest', 'interestRate', 'interest_rate'), 0);
$duration = (int) api_value($body, array('duration', 'durationDays', 'duration_days'), 0);

if ($userID === '') {
    frontend_format_error('Please log in again before deploying capital.', 401);
}

if ($amount <= 0) {
    frontend_format_error('Amount must be greater than zero.', 422);
}

if ($interest < 0 || $interest > 15) {
    frontend_format_error('Interest must be between 0 and 15 percent.', 422);
}

if ($duration <= 0) {
    frontend_format_error('Duration must be at least 1 day.', 422);
}

try {
    $stmt = $pdo->prepare('SELECT User_ID, Code_Name FROM Users WHERE User_ID = ? AND Is_Verified = TRUE LIMIT 1');
    $stmt->execute(array($userID));
    $user = $stmt->fetch();
} catch (PDOException $e) {
    frontend_format_error('Database error while checking account.', 500);
}

if (!$user) {
    frontend_format_error('Verified account not found. Please log in again.', 404);
}

$loanID = frontend_loan_id($pdo);
$today = date('Y-m-d');

try {
    $stmt = $pdo->prepare('
        INSERT INTO Loan
            (Loan_ID, Borrower_ID, Lender_ID, Amount, Duration_Months, Interest_Rate, Loan_Status, Date_Requested, Date_Disbursed, Purpose)
        VALUES
            (?, NULL, ?, ?, ?, ?, "approved", ?, NULL, "other")
    ');
    $stmt->execute(array($loanID, $userID, $amount, $duration, $interest, $today));
} catch (PDOException $e) {
    frontend_format_error('Failed to deploy capital.', 500);
}

api_json(array(
    'message' => 'Capital deployed successfully.',
    'vault' => array(
        'id' => $loanID,
        'amount' => $amount,
        'interest' => $interest,
        'duration' => $duration,
        'alias' => $user['Code_Name'],
    ),
), 201);
