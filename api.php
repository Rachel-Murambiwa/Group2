<?php

function api_cors($methods = 'GET, POST, OPTIONS') {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: ' . $methods . ', OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
}

function api_require_method($method) {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        api_json(array('success' => false, 'message' => 'Method not allowed.'), 405);
    }
}

function api_json($payload, $status = 200) {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function api_input() {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);

    if (is_array($json)) {
        return array_merge($_POST, $json);
    }

    return $_POST;
}

function api_value($source, $keys, $default = '') {
    foreach ($keys as $key) {
        if (isset($source[$key])) {
            return $source[$key];
        }
    }

    return $default;
}

function api_headers() {
    if (function_exists('getallheaders')) {
        return getallheaders();
    }

    $headers = array();
    foreach ($_SERVER as $name => $value) {
        if (str_starts_with($name, 'HTTP_')) {
            $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
            $headers[$key] = $value;
        }
    }
    return $headers;
}

function api_token() {
    $headers = api_headers();
    $token = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    $token = trim($token);

    if (stripos($token, 'Bearer ') === 0) {
        return trim(substr($token, 7));
    }

    return $token;
}

function api_bool($value) {
    return (bool) $value;
}

function api_number($value) {
    return $value === null ? null : (float) $value;
}

function api_user($user) {
    return array(
        'userID'      => $user['User_ID'],
        'id'          => $user['User_ID'],
        'userId'      => $user['User_ID'],
        'firstName'   => $user['First_Name'],
        'first_name'  => $user['First_Name'],
        'lastName'    => $user['Last_Name'],
        'last_name'   => $user['Last_Name'],
        'name'        => trim($user['First_Name'] . ' ' . $user['Last_Name']),
        'email'       => $user['Email'],
        'phoneNumber' => $user['Phone_Number'],
        'phone'       => $user['Phone_Number'],
        'phone_number' => $user['Phone_Number'],
        'bankName'    => $user['BankName'],
        'bank_name'   => $user['BankName'],
        'bankAccount' => $user['BankAccount'] ?? null,
        'bank_account' => $user['BankAccount'] ?? null,
        'codeName'    => $user['Code_Name'],
        'code_name'   => $user['Code_Name'],
        'creditScore' => api_number($user['Credit_Score'] ?? 5),
        'credit_score' => api_number($user['Credit_Score'] ?? 5),
        'isVerified'  => api_bool($user['Is_Verified'] ?? false),
        'is_verified' => api_bool($user['Is_Verified'] ?? false),
    );
}

function api_loan($loan) {
    return array(
        'loanID'         => (int) $loan['Loan_ID'],
        'id'             => (int) $loan['Loan_ID'],
        'loanId'         => (int) $loan['Loan_ID'],
        'loan_id'        => (int) $loan['Loan_ID'],
        'borrowerID'     => $loan['Borrower_ID'],
        'borrowerId'     => $loan['Borrower_ID'],
        'borrower_id'    => $loan['Borrower_ID'],
        'lenderID'       => $loan['Lender_ID'],
        'lenderId'       => $loan['Lender_ID'],
        'lender_id'      => $loan['Lender_ID'],
        'amount'         => api_number($loan['Amount']),
        'durationMonths' => (int) $loan['Duration_Months'],
        'duration'       => (int) $loan['Duration_Months'],
        'duration_months' => (int) $loan['Duration_Months'],
        'interestRate'   => api_number($loan['Interest_Rate']),
        'interest_rate'  => api_number($loan['Interest_Rate']),
        'status'         => $loan['Loan_Status'],
        'loanStatus'     => $loan['Loan_Status'],
        'loan_status'    => $loan['Loan_Status'],
        'dateRequested'  => $loan['Date_Requested'],
        'date_requested' => $loan['Date_Requested'],
        'dateDisbursed'  => $loan['Date_Disbursed'],
        'date_disbursed' => $loan['Date_Disbursed'],
        'purpose'        => $loan['Purpose'],
    );
}

function api_loans($loans) {
    return array_map('api_loan', $loans);
}

function api_repayment($repayment) {
    return array(
        'repaymentID' => (int) $repayment['Repayment_ID'],
        'id'          => (int) $repayment['Repayment_ID'],
        'repaymentId' => (int) $repayment['Repayment_ID'],
        'repayment_id' => (int) $repayment['Repayment_ID'],
        'loanID'      => (int) $repayment['Loan_ID'],
        'loanId'      => (int) $repayment['Loan_ID'],
        'loan_id'     => (int) $repayment['Loan_ID'],
        'dueDate'     => $repayment['Due_Date'],
        'due_date'    => $repayment['Due_Date'],
        'amountDue'   => api_number($repayment['Amount_Due']),
        'amount_due'  => api_number($repayment['Amount_Due']),
        'status'      => $repayment['Loan_Status'],
    );
}

function api_repayments($repayments) {
    return array_map('api_repayment', $repayments);
}

function api_transaction($transaction) {
    return array(
        'transactionID'   => (int) $transaction['Transaction_ID'],
        'id'              => (int) $transaction['Transaction_ID'],
        'transactionId'   => (int) $transaction['Transaction_ID'],
        'transaction_id'  => (int) $transaction['Transaction_ID'],
        'transactionType' => $transaction['Transaction_Type'],
        'transaction_type' => $transaction['Transaction_Type'],
        'loanID'          => (int) $transaction['Loan_ID'],
        'loanId'          => (int) $transaction['Loan_ID'],
        'loan_id'         => (int) $transaction['Loan_ID'],
        'transactionDate' => $transaction['Transaction_Date'],
        'transaction_date' => $transaction['Transaction_Date'],
    );
}

function api_transactions($transactions) {
    return array_map('api_transaction', $transactions);
}
