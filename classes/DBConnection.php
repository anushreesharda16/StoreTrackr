<?php
    class DatabaseConnect {
        private $servername = "localhost";
        private $username = "root";
        private $password = "";
        private $db_name = "storetrackr";
        public $conn;

        public function connect()
        {
            $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->db_name);
            if($this->conn->connect_error) {
                die("Connection Failed." );
            }
            return $this->conn;
        }
    }
?>