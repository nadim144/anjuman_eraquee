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

        // Check optional external db_config.php if present
        $customConfig = __DIR__ . '/db_config.php';
        if (file_exists($customConfig)) {
            @include $customConfig;
        }

        $isLocalhost = in_array(strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]), ['localhost', '127.0.0.1', '']) || php_sapi_name() === 'cli';

        $remoteConfigs = [];
        if (isset($infinityfree_config) && is_array($infinityfree_config)) {
            $remoteConfigs[] = $infinityfree_config;
        }

        $defaultConfigs = [
            ['127.0.0.1', 'root', '', 'codecxss_anjuman', 3307],
            ['localhost', 'root', '', 'codecxss_anjuman', 3307],
            ['127.0.0.1', 'codecxss_anjuman', 'anjuman!@#2021', 'codecxss_anjuman', 3307],
            ['localhost', 'codecxss_anjuman', 'anjuman!@#2021', 'codecxss_anjuman', 3306],
            ['localhost', 'root', '', 'codecxss_anjuman', 3306],
            ['localhost', 'root', '', 'anjuman_user', 3306]
        ];

        $dbConfigs = $isLocalhost 
            ? array_merge($defaultConfigs, $remoteConfigs) 
            : array_merge($remoteConfigs, $defaultConfigs);

        foreach ($dbConfigs as $cfg) {
            $port = isset($cfg[4]) ? $cfg[4] : 3306;
            $c = @mysqli_connect($cfg[0], $cfg[1], $cfg[2], $cfg[3], $port);
            if ($c) {
                $conn = $c;
                run_db_migrations($conn);
                return $conn;
            }
        }
        return false;
    }
}

if (!function_exists('run_db_migrations')) {
    function run_db_migrations($conn) {
        static $migrated = false;
        if ($migrated) return;

        // Auto-create user_registrtion table if it doesn't exist yet
        $createTableSql = "CREATE TABLE IF NOT EXISTS `user_registrtion` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `username` varchar(255) DEFAULT NULL,
          `fathername` varchar(255) DEFAULT NULL,
          `mothername` varchar(255) DEFAULT NULL,
          `grandfathername` varchar(255) DEFAULT NULL,
          `nativeplace` varchar(255) DEFAULT NULL,
          `age` varchar(50) DEFAULT NULL,
          `dob` date DEFAULT NULL,
          `gender` varchar(50) DEFAULT NULL,
          `maritalstatus` varchar(50) DEFAULT NULL,
          `presentaddress` text DEFAULT NULL,
          `presentvillatpost` varchar(255) DEFAULT NULL,
          `presentdistrict` varchar(255) DEFAULT NULL,
          `presentpincode` varchar(50) DEFAULT NULL,
          `presentstate` varchar(255) DEFAULT NULL,
          `presentcountry` varchar(255) DEFAULT NULL,
          `presentaddresstopermanent` varchar(50) DEFAULT NULL,
          `permanentaddress` text DEFAULT NULL,
          `permanentvillatpost` varchar(255) DEFAULT NULL,
          `permanentdistrict` varchar(255) DEFAULT NULL,
          `permanentpincode` varchar(50) DEFAULT NULL,
          `permanentstate` varchar(255) DEFAULT NULL,
          `permanentcountry` varchar(255) DEFAULT NULL,
          `email` varchar(255) DEFAULT NULL,
          `phonenumber` varchar(100) DEFAULT NULL,
          `whatsappnumber` varchar(100) DEFAULT NULL,
          `qulification` varchar(255) DEFAULT NULL,
          `qualificationdetails` text DEFAULT NULL,
          `occupation` varchar(255) DEFAULT NULL,
          `occupationdetails` text DEFAULT NULL,
          `messageinfo` text DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `otp_code` varchar(10) DEFAULT NULL,
          `otp_expiry` datetime DEFAULT NULL,
          `password` varchar(255) DEFAULT NULL,
          `is_temp_password` tinyint(1) DEFAULT 0,
          `reset_requested` tinyint(1) DEFAULT 0,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        @mysqli_query($conn, $createTableSql);

        // Fetch currently existing columns to safely alter without version incompatibility
        $existingCols = [];
        $colRes = @mysqli_query($conn, "SHOW COLUMNS FROM `user_registrtion`");
        if ($colRes) {
            while ($row = mysqli_fetch_assoc($colRes)) {
                $existingCols[strtolower($row['Field'])] = true;
            }
        }

        $neededCols = [
            'dob' => "ALTER TABLE `user_registrtion` ADD COLUMN `dob` DATE NULL AFTER `age`",
            'password' => "ALTER TABLE `user_registrtion` ADD COLUMN `password` VARCHAR(255) NULL",
            'otp_code' => "ALTER TABLE `user_registrtion` ADD COLUMN `otp_code` VARCHAR(10) NULL",
            'otp_expiry' => "ALTER TABLE `user_registrtion` ADD COLUMN `otp_expiry` DATETIME NULL",
            'is_temp_password' => "ALTER TABLE `user_registrtion` ADD COLUMN `is_temp_password` TINYINT(1) DEFAULT 0",
            'reset_requested' => "ALTER TABLE `user_registrtion` ADD COLUMN `reset_requested` TINYINT(1) DEFAULT 0",
            'profile_picture' => "ALTER TABLE `user_registrtion` ADD COLUMN `profile_picture` VARCHAR(255) NULL AFTER `username`",
            'cast' => "ALTER TABLE `user_registrtion` ADD COLUMN `cast` VARCHAR(100) NULL AFTER `maritalstatus`",
            'aadhaar_number' => "ALTER TABLE `user_registrtion` ADD COLUMN `aadhaar_number` VARCHAR(20) NULL AFTER `dob`",
            'additional_mobile' => "ALTER TABLE `user_registrtion` ADD COLUMN `additional_mobile` VARCHAR(20) NULL AFTER `phonenumber`",
            'certificate_path' => "ALTER TABLE `user_registrtion` ADD COLUMN `certificate_path` VARCHAR(255) NULL",
            'certificate_generated_at' => "ALTER TABLE `user_registrtion` ADD COLUMN `certificate_generated_at` DATETIME NULL",
            'registration_step' => "ALTER TABLE `user_registrtion` ADD COLUMN `registration_step` TINYINT(1) DEFAULT 1",
            'is_profile_completed' => "ALTER TABLE `user_registrtion` ADD COLUMN `is_profile_completed` TINYINT(1) DEFAULT 0"
        ];

        foreach ($neededCols as $colName => $alterSql) {
            if (!isset($existingCols[$colName])) {
                @mysqli_query($conn, $alterSql);
            }
        }

        $migrated = true;
    }
}

