<?php

require_once 'db.php';
api_cors('GET');
api_require_method('GET');
require_once 'auth.php';

try {
    $stmt = $pdo->prepare('
        SELECT * FROM Loan
        WHERE Loan_Status = "approved" AND Borrower_ID <> ?
        ORDER BY Date_Requested DESC
    ');
    $stmt->execute(array($loggedInUser['User_ID']));
    $loans = $stmt->fetchAll();
} catch (PDOException $e) {
    api_json(array('success' => false, 'message' => 'Error fetching available loans: ' . $e->getMessage()), 500);
}

api_json(array(
    'success' => true,
    'loans'   => api_loans($loans),
));
