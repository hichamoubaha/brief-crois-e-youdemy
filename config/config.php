<?php
class Database {
    private static $instance = null;
    private $pdo;
    private $host = "localhost";
    private $db_name = "youdemy_v2";
    private $username = "root";
    private $password = "";

    private function __construct() {
        $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name;
        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function connect() {
        return $this->pdo;
    }
}
?>

