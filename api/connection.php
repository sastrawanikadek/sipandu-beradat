<?php
    define("HOST", "localhost");
    define("USERNAME", "gusti_trisna");
    define("PASSWORD", "4dmin@Trisna");
    define("DB_NAME", "sipandu_beradat");

    function connect_db() {
        $mysqli = new mysqli(HOST, USERNAME, PASSWORD, DB_NAME);

        if ($mysqli->connect_errno) {
            echo "MySQL Connect Error: " . $mysqli->connect_error;
            exit();
        }

        return $mysqli;
    }
    