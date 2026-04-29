<?php
// Rules:
// - Default neutral value: 5.0
// - On repayment:          increase by 0.5 (max 10)
// - On default (late):     decrease by 1.0 (min 0)

//when a user successfully repays
function increaseCredit($pdo, $userID) {
    try {
        $stmt = $pdo->prepare('SELECT Credit_Score FROM Users WHERE User_ID = ?');
        $stmt->execute(array($userID));
        $user  = $stmt->fetch();
        $score = $user['Credit_Score'] ?? 5.0;

        // Increase by 0.5, cap at 10
        $newScore = min(10, $score + 0.5);

        $stmt = $pdo->prepare('UPDATE Users SET Credit_Score = ? WHERE User_ID = ?');
        $stmt->execute(array($newScore, $userID));

        return $newScore;
    } catch (PDOException $e) {
        return null;
    }
}

//when a user defaults on a loan
function decreaseCredit($pdo, $userID) {
    try {
        $stmt = $pdo->prepare('SELECT Credit_Score FROM Users WHERE User_ID = ?');
        $stmt->execute(array($userID));
        $user  = $stmt->fetch();
        $score = $user['Credit_Score'] ?? 5.0;

        // Decrease by 1.0, floor at 0
        $newScore = max(0, $score - 1.0);

        $stmt = $pdo->prepare('UPDATE Users SET Credit_Score = ? WHERE User_ID = ?');
        $stmt->execute(array($newScore, $userID));

        return $newScore;
    } catch (PDOException $e) {
        return null;
    }
}