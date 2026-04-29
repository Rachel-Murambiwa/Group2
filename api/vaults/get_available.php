<?php

require_once __DIR__ . '/../../frontend_api.php';

api_cors('GET');
api_require_method('GET');

try {
    $stmt = $pdo->prepare('
        SELECT
            l.Loan_ID AS id,
            l.Amount AS amount,
            l.Interest_Rate AS interest,
            l.Duration_Months AS duration,
            COALESCE(u.Code_Name, "Vault") AS alias
        FROM Loan l
        LEFT JOIN Users u ON u.User_ID = l.Lender_ID
        WHERE l.Loan_Status = "approved"
        ORDER BY l.Date_Requested DESC, l.Loan_ID DESC
    ');
    $stmt->execute();
    $vaults = $stmt->fetchAll();
} catch (PDOException $e) {
    frontend_format_error('Database error while fetching vaults.', 500);
}

$vaults = array_map(function ($vault) {
    return array(
        'id' => (int) $vault['id'],
        'amount' => (float) $vault['amount'],
        'interest' => (float) $vault['interest'],
        'duration' => (int) $vault['duration'],
        'alias' => $vault['alias'],
    );
}, $vaults);

api_json(array('vaults' => $vaults));
