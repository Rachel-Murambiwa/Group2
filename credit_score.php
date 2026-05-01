<?php
// Credit scoring system for the new schema
// Since the users table doesn't have a credit_score field,
// we'll calculate it based on transaction history

function calculateCreditScore($pdo, $userId) {
    try {
        // Get user's transaction history
        $stmt = $pdo->prepare('
            SELECT type, amount, created_at 
            FROM transactions 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ');
        $stmt->execute(array($userId));
        $transactions = $stmt->fetchAll();

        // Base score
        $score = 5.0;

        // Analyze transactions
        foreach ($transactions as $transaction) {
            switch ($transaction['type']) {
                case 'repayment':
                    $score += 0.5; // Increase for on-time repayments
                    break;
                case 'repayment_late':
                    $score -= 1.0; // Decrease for late repayments
                    break;
                case 'loan_disbursed':
                    // Neutral - just taking a loan doesn't affect score
                    break;
            }
        }

        // Cap between 0 and 10
        return max(0, min(10, $score));
    } catch (PDOException $e) {
        return 5.0; // Default score on error
    }
}

function getCreditScore($pdo, $userId) {
    return calculateCreditScore($pdo, $userId);
}