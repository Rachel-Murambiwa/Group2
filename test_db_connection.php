<?php
// Try different database hosts
$hosts = ['db', 'localhost', '127.0.0.1'];
$connected = false;

foreach ($hosts as $host) {
    try {
        echo "Trying host: $host\n";
        $pdo = new PDO("mysql:host=$host;dbname=charleedash_db", 'root', 'Chacha@1583');
        echo "Database connection: SUCCESS with host $host\n";
        $connected = true;
        
        $stmt = $pdo->query('SHOW TABLES');
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Available tables: " . implode(', ', $tables) . "\n";
        
        // Test users table structure if it exists
        if (in_array('users', $tables)) {
            $stmt = $pdo->query('DESCRIBE users');
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "\nUsers table structure:\n";
            foreach ($columns as $column) {
                echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
            }
        }
        break;
    } catch (Exception $e) {
        echo "Failed with host $host: " . $e->getMessage() . "\n";
    }
}

if (!$connected) {
    echo "\nNo database connection could be established. Please check:\n";
    echo "1. MySQL/MariaDB is running\n";
    echo "2. Database 'charleedash_db' exists\n";
    echo "3. Credentials are correct\n";
}