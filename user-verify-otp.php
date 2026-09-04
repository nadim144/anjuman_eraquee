<?php
session_start();

if (!isset($_SESSION['pending_phone']) || !isset($_SESSION['pending_otp'])) {
    header('Location: user-login.php');
    exit;
}

$phone = $_SESSION['pending_phone'];
$correctOtp = $_SESSION['pending_otp'];
$expiry = $_SESSION['otp_expiry'] ?? 0;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredOtp = trim($_POST['otp'] ?? '');

    if (time() > $expiry) {
        $error = 'OTP has expired. Please request a new OTP.';
    } else if ($enteredOtp == $correctOtp || $enteredOtp === '123456') { // 123456 as master dev OTP
        // OTP Verified successfully!
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_phone'] = $phone;
        $_SESSION['user_id'] = $_SESSION['pending_user_id'];

        // Clean up pending session data
        unset($_SESSION['pending_otp']);
        unset($_SESSION['pending_phone']);
        unset($_SESSION['pending_user_id']);
        unset($_SESSION['otp_expiry']);

        header('Location: user-dashboard.php');
        exit;
    } else {
        $error = 'Invalid OTP code. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP | Anjuman Eraquee INDIA</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Droid Sans', sans-serif;
        }
        .otp-container {
            max-width: 420px;
            margin: 80px auto;
            background: #ffffff;
            padding: 35px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-top: 4px solid #009146;
        }
        .otp-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .otp-header h3 {
            font-size: 22px;
            font-weight: 700;
            color: #009146;
            margin: 0;
        }
        .otp-header p {
            color: #666;
            font-size: 13px;
            margin-top: 5px;
        }
        .dev-badge {
            background-color: #e8f5e9;
            border: 1px dashed #009146;
            color: #007638;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            text-align: center;
            margin-bottom: 20px;
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
        .otp-input {
            letter-spacing: 6px;
            font-size: 20px;
            text-align: center;
            font-weight: 700;
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
    </style>
</head>
<body>

<div class="container">
    <div class="otp-container">
        <div class="otp-header">
            <h3>Verify OTP</h3>
            <p>OTP sent to <strong>+91 <?php echo htmlspecialchars($phone); ?></strong></p>
        </div>

        <!-- Dev Mode OTP Display -->
        <div class="dev-badge">
            <i class="fa fa-info-circle"></i> <strong>Dev Mode OTP:</strong> <span style="font-size:16px; font-weight:bold; letter-spacing:2px; color:#d9534f;"><?php echo htmlspecialchars($correctOtp); ?></span>
            <br><small class="text-muted">(Use this code above or 123456 to log in)</small>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger font-weight-bold" style="font-size: 14px;">
                <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="user-verify-otp.php" method="POST">
            <div class="form-group">
                <label for="otp" class="font-weight-bold">Enter 6-Digit OTP</label>
                <input type="text" name="otp" id="otp" class="form-control otp-input" placeholder="------" maxlength="6" required autofocus autocomplete="off">
            </div>

            <button type="submit" class="btn btn-custom mt-3">
                <i class="fa fa-check-circle"></i> Verify & Login
            </button>
        </form>

        <div class="footer-link">
            Didn't receive code? <a href="user-login.php">Resend OTP</a><br>
            <a href="user-login.php" class="text-muted mt-2 d-inline-block"><i class="fa fa-arrow-left"></i> Change Mobile Number</a>
        </div>
    </div>
</div>

</body>
</html>

