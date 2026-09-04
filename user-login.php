<?php
session_start();

require_once __DIR__ . '/db.php';
$conn = get_db_connection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phonenumber'] ?? '');
    // Clean phone (digits only)
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

    if (empty($cleanPhone)) {
        $error = 'Please enter a valid mobile number.';
    } else if (!$conn) {
        $error = 'Database connection error. Please try again later.';
    } else {
        // Check if user exists with this phone number
        $cleanPhoneEscaped = mysqli_real_escape_string($conn, $cleanPhone);
        $phoneEscaped = mysqli_real_escape_string($conn, $phone);

        $query = "SELECT * FROM user_registrtion WHERE phonenumber LIKE '%$cleanPhoneEscaped%' OR phonenumber LIKE '%$phoneEscaped%' LIMIT 1";
        $res = mysqli_query($conn, $query);

        if ($res && mysqli_num_rows($res) > 0) {
            $user = mysqli_fetch_assoc($res);
            
            // Generate 6-digit OTP
            $otp = rand(100000, 999999);
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            // Store in session for validation
            $_SESSION['pending_otp'] = $otp;
            $_SESSION['pending_phone'] = $user['phonenumber'];
            $_SESSION['pending_user_id'] = $user['id'];
            $_SESSION['otp_expiry'] = time() + 600; // 10 mins

            // Ensure otp columns exist or update if possible
            @mysqli_query($conn, "ALTER TABLE user_registrtion ADD COLUMN IF NOT EXISTS otp_code VARCHAR(10)");
            @mysqli_query($conn, "ALTER TABLE user_registrtion ADD COLUMN IF NOT EXISTS otp_expiry DATETIME");
            @mysqli_query($conn, "UPDATE user_registrtion SET otp_code='$otp', otp_expiry='$expiry' WHERE id=" . intval($user['id']));

            header('Location: user-verify-otp.php');
            exit;
        } else {
            $error = 'Mobile number not found. Please register first.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login | Anjuman Eraquee INDIA</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Droid Sans', sans-serif;
        }
        .login-container {
            max-width: 420px;
            margin: 80px auto;
            background: #ffffff;
            padding: 35px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-top: 4px solid #009146;
        }
        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .login-header img {
            max-height: 70px;
            margin-bottom: 12px;
        }
        .login-header h3 {
            font-size: 22px;
            font-weight: 700;
            color: #009146;
            margin: 0;
        }
        .login-header p {
            color: #666;
            font-size: 13px;
            margin-top: 5px;
        }
        .btn-custom {
            background-color: #009146;
            color: #fff;
            font-weight: 600;
            padding: 12px;
            border-radius: 4px;
            width: 100%;
            border: none;
            transition: all 0.3s;
        }
        .btn-custom:hover {
            background-color: #007638;
            color: #fff;
        }
        .form-control:focus {
            border-color: #009146;
            box-shadow: 0 0 0 0.2rem rgba(0, 145, 70, 0.25);
        }
        .footer-link {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
        }
        .footer-link a {
            color: #009146;
            font-weight: 600;
            text-decoration: none;
        }
        .footer-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="login-container">
        <div class="login-header">
            <a href="index.html"><img src="images/logo.png" alt="Anjuman Logo" onerror="this.style.display='none'"></a>
            <h3>Member Login</h3>
            <p>Enter your registered mobile number to receive OTP</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger font-weight-bold" style="font-size: 14px;">
                <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="user-login.php" method="POST">
            <div class="form-group">
                <label for="phonenumber" class="font-weight-bold">Mobile Number</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-phone"></i></span>
                    </div>
                    <input type="text" name="phonenumber" id="phonenumber" class="form-control" placeholder="Enter your 10-digit mobile number" required autofocus>
                </div>
            </div>

            <button type="submit" class="btn btn-custom mt-3">
                <i class="fa fa-paper-plane"></i> Send OTP
            </button>
        </form>

        <div class="footer-link">
            Not registered yet? <a href="registration.html">Join Membership</a><br>
            <a href="index.html" class="text-muted mt-2 d-inline-block"><i class="fa fa-arrow-left"></i> Back to Homepage</a>
        </div>
    </div>
</div>

</body>
</html>

