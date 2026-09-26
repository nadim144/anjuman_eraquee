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

        // Automated creation of admin_users table for Role-Based Access Control (RBAC)
        $createAdminTableSql = "CREATE TABLE IF NOT EXISTS `admin_users` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `role` enum('super_admin','admin') NOT NULL DEFAULT 'admin',
          `status` enum('active','inactive') NOT NULL DEFAULT 'active',
          `created_by` int(11) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `last_login` datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq_admin_user` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        @mysqli_query($conn, $createAdminTableSql);

        // Ensure primary Super Admin (ahmad.nadim144@gmail.com) is designated as super_admin
        $superEmail = 'ahmad.nadim144@gmail.com';
        $userCheck = @mysqli_query($conn, "SELECT id FROM `user_registrtion` WHERE `email` = '$superEmail' LIMIT 1");
        if ($userCheck && $uRow = mysqli_fetch_assoc($userCheck)) {
            $superUserId = intval($uRow['id']);
            $adminCheck = @mysqli_query($conn, "SELECT id, role FROM `admin_users` WHERE `user_id` = $superUserId LIMIT 1");
            if ($adminCheck && mysqli_num_rows($adminCheck) === 0) {
                @mysqli_query($conn, "INSERT INTO `admin_users` (`user_id`, `role`, `status`) VALUES ($superUserId, 'super_admin', 'active')");
            } else if ($adminCheck && $aRow = mysqli_fetch_assoc($adminCheck)) {
                if ($aRow['role'] !== 'super_admin') {
                    @mysqli_query($conn, "UPDATE `admin_users` SET `role` = 'super_admin', `status` = 'active' WHERE `id` = " . intval($aRow['id']));
                }
            }
        }

        // Automated creation of Matrimonial System tables
        $createMatrimonialProfilesSql = "CREATE TABLE IF NOT EXISTS `matrimonial_profiles` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `profile_code` varchar(20) DEFAULT NULL,
          `created_by_user_id` int(11) NOT NULL,
          `profile_for` enum('self','son','daughter','brother','sister','relative') NOT NULL DEFAULT 'self',
          `gender` enum('male','female') NOT NULL DEFAULT 'male',
          `full_name` varchar(255) NOT NULL,
          `dob` date DEFAULT NULL,
          `age` int(11) DEFAULT NULL,
          `height` varchar(20) DEFAULT NULL,
          `marital_status` enum('unmarried','divorced','khula_shuda','widowed') NOT NULL DEFAULT 'unmarried',
          `complexion` varchar(50) DEFAULT NULL,
          `mother_tongue` varchar(50) DEFAULT 'Urdu',
          `cast` varchar(100) DEFAULT 'Eraquee(Iraqi)',
          `sect` varchar(100) DEFAULT 'Sunni',
          `qualification` varchar(255) DEFAULT NULL,
          `occupation` varchar(255) DEFAULT NULL,
          `employed_in` varchar(100) DEFAULT NULL,
          `annual_income` varchar(100) DEFAULT NULL,
          `work_city` varchar(100) DEFAULT NULL,
          `work_state` varchar(100) DEFAULT NULL,
          `father_name` varchar(255) DEFAULT NULL,
          `father_occupation` varchar(255) DEFAULT NULL,
          `mother_name` varchar(255) DEFAULT NULL,
          `mother_occupation` varchar(255) DEFAULT NULL,
          `brothers_count` int(11) DEFAULT 0,
          `sisters_count` int(11) DEFAULT 0,
          `family_type` varchar(50) DEFAULT 'Nuclear',
          `family_values` varchar(50) DEFAULT 'Traditional',
          `native_place` varchar(255) DEFAULT NULL,
          `present_city` varchar(255) DEFAULT NULL,
          `present_state` varchar(255) DEFAULT NULL,
          `full_address` text DEFAULT NULL,
          `contact_person_name` varchar(255) DEFAULT NULL,
          `contact_relation` varchar(100) DEFAULT NULL,
          `contact_phone` varchar(50) DEFAULT NULL,
          `contact_whatsapp` varchar(50) DEFAULT NULL,
          `partner_preferences` text DEFAULT NULL,
          `about_candidate` text DEFAULT NULL,
          `primary_photo` varchar(255) DEFAULT NULL,
          `hide_photo_completely` tinyint(1) DEFAULT 0,
          `status` enum('pending_approval','active','hidden','married') NOT NULL DEFAULT 'pending_approval',
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_user_id` (`created_by_user_id`),
          KEY `idx_gender_status` (`gender`,`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        @mysqli_query($conn, $createMatrimonialProfilesSql);

        $createMatrimonialPhotosSql = "CREATE TABLE IF NOT EXISTS `matrimonial_photos` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `profile_id` int(11) NOT NULL,
          `photo_path` varchar(255) NOT NULL,
          `is_primary` tinyint(1) DEFAULT 0,
          `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_photo_profile` (`profile_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        @mysqli_query($conn, $createMatrimonialPhotosSql);

        $createMatrimonialInterestsSql = "CREATE TABLE IF NOT EXISTS `matrimonial_interests` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `sender_user_id` int(11) NOT NULL,
          `sender_profile_id` int(11) NOT NULL,
          `receiver_user_id` int(11) NOT NULL,
          `receiver_profile_id` int(11) NOT NULL,
          `message` varchar(255) DEFAULT NULL,
          `status` enum('pending','accepted','declined') NOT NULL DEFAULT 'pending',
          `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `responded_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq_interest` (`sender_profile_id`,`receiver_profile_id`),
          KEY `idx_receiver_profile` (`receiver_profile_id`),
          KEY `idx_sender_profile` (`sender_profile_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        @mysqli_query($conn, $createMatrimonialInterestsSql);

        $createMatrimonialRequestsSql = "CREATE TABLE IF NOT EXISTS `matrimonial_access_requests` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `requester_user_id` int(11) NOT NULL,
          `target_profile_id` int(11) NOT NULL,
          `requester_profile_id` int(11) DEFAULT NULL,
          `status` enum('pending','approved_by_admin','rejected_by_admin') NOT NULL DEFAULT 'pending',
          `admin_notes` text DEFAULT NULL,
          `reviewed_by_admin_id` int(11) DEFAULT NULL,
          `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `reviewed_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_target_profile` (`target_profile_id`),
          KEY `idx_requester_user` (`requester_user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        @mysqli_query($conn, $createMatrimonialRequestsSql);

        // Ensure matrimonial upload directory exists
        $matrimonialUploadDir = __DIR__ . '/uploads/matrimonial';
        if (!is_dir($matrimonialUploadDir)) {
            @mkdir($matrimonialUploadDir, 0755, true);
        }

        $migrated = true;
    }
}

