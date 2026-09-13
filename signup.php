<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';
$conn = get_db_connection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed. Please try again later.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Support both FormData / urlencoded and JSON body
$phone = trim($_POST['phonenumber'] ?? $_POST['mobile'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (empty($phone) && empty($email) && empty($password)) {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    if (is_array($input)) {
        $phone = trim($input['phonenumber'] ?? $input['mobile'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $confirmPassword = $input['confirm_password'] ?? '';
    }
}

// Clean phone (digits only)
$cleanPhone = preg_replace('/[^0-9]/', '', $phone);
if (strlen($cleanPhone) < 10) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid 10-digit mobile number.']);
    exit;
}
if (strlen($cleanPhone) > 10) {
    // Keep last 10 digits if country code was provided (e.g. 919876543210)
    $cleanPhone = substr($cleanPhone, -10);
}

// Validate Email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// Validate Password
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long.']);
    exit;
}

if ($password !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match. Please re-enter.']);
    exit;
}

$phoneEsc = mysqli_real_escape_string($conn, $cleanPhone);
$emailEsc = mysqli_real_escape_string($conn, $email);

// Check if user already exists
$checkSql = "SELECT id, username FROM user_registrtion WHERE phonenumber = '$phoneEsc' OR email = '$emailEsc' LIMIT 1";
$checkRes = mysqli_query($conn, $checkSql);

if ($checkRes && mysqli_num_rows($checkRes) > 0) {
    echo json_encode([
        'success' => false,
        'already_exists' => true,
        'message' => 'An account with this Mobile Number or Email already exists. Please log in.'
    ]);
    exit;
}

// Create new user record
$hash = password_hash($password, PASSWORD_BCRYPT);
$hashEsc = mysqli_real_escape_string($conn, $hash);

$insertSql = "INSERT INTO user_registrtion (
    phonenumber, email, password, registration_step, is_profile_completed, created_at
) VALUES (
    '$phoneEsc', '$emailEsc', '$hashEsc', 1, 0, NOW()
)";

if (mysqli_query($conn, $insertSql)) {
    $userId = mysqli_insert_id($conn);

    // Establish user session
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_phone'] = $cleanPhone;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = '';

    echo json_encode([
        'success' => true,
        'message' => 'Sign up successful! Redirecting to complete your registration...',
        'redirect' => 'registration.php'
    ]);
    exit;
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Registration error: ' . mysqli_error($conn)
    ]);
    exit;
}

