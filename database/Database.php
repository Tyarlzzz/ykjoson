<?php
    class Database {
        private $host = 'localhost';
        private $dbname = 'ykjoson'; 
        private $username = 'root'; 
        private $password = 'danielletony'; 
        private $conn;

        public function getConnection() {
            if ($this->conn == null) {
                try {
                    $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (PDOException $e) {
                    die("Connection failed: " . $e->getMessage());
                }
            }
            return $this->conn;
        }
    }
?>