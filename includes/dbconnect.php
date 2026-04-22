<?php
    class dbconnect{
        private $con;
        function __construct(){
            require_once dirname(__FILE__).'/constants.php';
            $this->con = $this->connect();
        }
        function connect(){
            include_once dirname(__FILE__).'/constants.php';
            

            $host = defined('DB_HOST') ? DB_HOST : 'localhost';
            $user = defined('DB_USER') ? DB_USER : '';
            $pass = defined('DB_PASSWORD') ? DB_PASSWORD : '';
            $name = defined('DB_NAME') ? DB_NAME : '';
            $socket = defined('DB_SOCKET') ? DB_SOCKET : null;

            $this->con = new mysqli($host, $user, $pass, $name, 0, $socket);

            if ($this->con->connect_errno) {
                echo "Failed to connect to MySQL: " . $this->con->connect_error;
            }
            return $this->con;
        }
    }
?>