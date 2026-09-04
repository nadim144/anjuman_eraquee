<?php
// Centralized Database Connection Helper
if (!function_exists('get_db_connection')) {
    function get_db_connection() {
        static $conn = null;
        if ($conn !== null && $conn !== false) {
            if (@mysqli_ping($conn)) {
                return $conn;
            }
        }

        $dbConfigs = [
            ['127.0.0.1', 'root', '', 'codecxss_anjuman', 3307],
            ['localhost', 'root', '', 'codecxss_anjuman', 3307],
            ['127.0.0.1', 'codecxss_anjuman', 'anjuman!@#2021', 'codecxss_anjuman', 3307],
            ['localhost', 'codecxss_anjuman', 'anjuman!@#2021', 'codecxss_anjuman', 3306],
            ['localhost', 'root', '', 'codecxss_anjuman', 3306],
            ['localhost', 'root', '', 'anjuman_user', 3306]
        ];

        foreach ($dbConfigs as $cfg) {
            $port = isset($cfg[4]) ? $cfg[4] : 3306;
            $c = @mysqli_connect($cfg[0], $cfg[1], $cfg[2], $cfg[3], $port);
            if ($c) {
                $conn = $c;
                return $conn;
            }
        }
        return false;
    }
}

