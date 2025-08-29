<?php
// bd.php - Secure database connection
class Database {
    private $host = "localhost";
    private $dbname = "bdophtalmos";
    private $username = "root"; // Replace with your DB username
    private $password = "Myros@1"; // Replace with your DB password
    private $conn;

    public function connect() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            return $this->conn;
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
}

// Create instance
$db = new Database();
$conn = $db->connect();
?>