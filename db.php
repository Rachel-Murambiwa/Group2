<?php
// db.php (in your root folder)
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $host = "db"; 
        $db_name = "charleedash_db";
        $username = "root";
        $password = "Chacha@1583";
        
        $this->conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->conn;
    }
}
?>