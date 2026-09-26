<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

/**
 * Ensures user is authenticated as an administrator.
 */
function check_admin_auth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Checks if the currently logged-in administrator is a Super Admin.
 * @return bool
 */
function is_super_admin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin';
}

/**
 * Enforces Super Admin access. If regular admin or not logged in, redirects.
 */
function require_super_admin() {
    check_admin_auth();
    if (!is_super_admin()) {
        header('Location: index.php?error=unauthorized');
        exit;
    }
}

/**
 * Returns formatted role display label.
 * @return string
 */
function get_logged_admin_role_label() {
    return is_super_admin() ? 'Super Admin' : 'Admin';
}
?>

