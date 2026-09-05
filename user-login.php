<?php
session_start();

require_once __DIR__ . '/db.php';
$conn = get_db_connection();

$error = '';
$success = '';
$activeTab = 'password';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login_password';

    if ($action === 'login_password') {
        $activeTab = 'password';
        $loginId = trim($_POST['login_id'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($loginId) || empty($password)) {
            $error = 'Please enter both your Mobile Number/Email and Password.';
        } else if (!$conn) {
            $error = 'Database connection error. Please try again later.';
        } else {
            $loginEsc = mysqli_real_escape_string($conn, $loginId);
            $cleanPhone = preg_replace('/[^0-9]/', '', $loginId);
            $cleanEsc = !empty($cleanPhone) ? mysqli_real_escape_string($conn, $cleanPhone) : '';

            $wherePhone = !empty($cleanEsc) ? "OR phonenumber LIKE '%$cleanEsc%'" : "";
            $sql = "SELECT * FROM user_registrtion WHERE email = '$loginEsc' OR phonenumber = '$loginEsc' $wherePhone LIMIT 1";
            $res = mysqli_query($conn, $sql);

            if ($res && mysqli_num_rows($res) > 0) {
                $user = mysqli_fetch_assoc($res);
                $isPasswordValid = false;

                if (!empty($user['password'])) {
                    if (password_verify($password, $user['password'])) {
                        $isPasswordValid = true;
                    } else if ($user['password'] === $password) {
                        $isPasswordValid = true;
                    }
                }

                if ($isPasswordValid) {
                    $_SESSION['user_logged_in'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_phone'] = $user['phonenumber'];
                    $_SESSION['user_name'] = $user['username'];
                    $_SESSION['user_email'] = $user['email'];

                    if (!empty($user['is_temp_password']) && intval($user['is_temp_password']) === 1) {
                        header('Location: user-reset-password.php');
                        exit;
                    } else {
                        header('Location: user-dashboard.php');
                        exit;
                    }
                } else {
                    $error = 'Incorrect password. If you forgot your password, please request a temporary password from Admin below.';
                }
            } else {
                $error = 'No account found with this Mobile Number or Email. Please register first.';
            }
        }
    } else if ($action === 'send_otp') {
        $activeTab = 'otp';
        $phone = trim($_POST['phonenumber'] ?? '');
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        if (empty($cleanPhone)) {
            $error = 'Please enter a valid mobile number.';
        } else if (!$conn) {
            $error = 'Database connection error. Please try again later.';
        } else {
            $cleanPhoneEscaped = mysqli_real_escape_string($conn, $cleanPhone);
            $phoneEscaped = mysqli_real_escape_string($conn, $phone);

            $query = "SELECT * FROM user_registrtion WHERE phonenumber LIKE '%$cleanPhoneEscaped%' OR phonenumber LIKE '%$phoneEscaped%' LIMIT 1";
            $res = mysqli_query($conn, $query);

            if ($res && mysqli_num_rows($res) > 0) {
                $user = mysqli_fetch_assoc($res);
                $otp = rand(100000, 999999);
                $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                $_SESSION['pending_otp'] = $otp;
                $_SESSION['pending_phone'] = $user['phonenumber'];
                $_SESSION['pending_user_id'] = $user['id'];
                $_SESSION['otp_expiry'] = time() + 600;

                @mysqli_query($conn, "UPDATE user_registrtion SET otp_code='$otp', otp_expiry='$expiry' WHERE id=" . intval($user['id']));

                header('Location: user-verify-otp.php');
                exit;
            } else {
                $error = 'Mobile number not found. Please register first.';
            }
        }
    } else if ($action === 'request_reset') {
        $activeTab = 'password';
        $resetId = trim($_POST['reset_id'] ?? '');

        if (empty($resetId)) {
            $error = 'Please enter your registered Mobile Number or Email to request a password reset.';
        } else if (!$conn) {
            $error = 'Database connection error. Please try again.';
        } else {
            $resetEsc = mysqli_real_escape_string($conn, $resetId);
            $cleanPhone = preg_replace('/[^0-9]/', '', $resetId);
            $cleanEsc = !empty($cleanPhone) ? mysqli_real_escape_string($conn, $cleanPhone) : '';

            $wherePhone = !empty($cleanEsc) ? "OR phonenumber LIKE '%$cleanEsc%'" : "";
            $sql = "SELECT * FROM user_registrtion WHERE email = '$resetEsc' OR phonenumber = '$resetEsc' $wherePhone LIMIT 1";
            $res = mysqli_query($conn, $sql);

            if ($res && mysqli_num_rows($res) > 0) {
                $user = mysqli_fetch_assoc($res);
                mysqli_query($conn, "UPDATE user_registrtion SET reset_requested = 1 WHERE id = " . intval($user['id']));
                $success = 'Your password reset request has been submitted to Admin! Please contact Admin (+91 9006297386) to obtain your temporary password.';
            } else {
                $error = 'No registered account found with that Mobile Number or Email.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Login | Anjuman Eraquee INDIA</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Droid Sans', sans-serif;
        }
        .login-container {
            max-width: 440px;
            margin: 60px auto;
            background: #ffffff;
            padding: 35px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-top: 4px solid #009146;
        }
        .login-header {
            text-align: center;
            margin-bottom: 22px;
        }
        .login-header img {
            max-height: 65px;
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
        .nav-pills .nav-link {
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
            color: #555;
            background: #f1f5f9;
            margin: 0 3px;
            text-align: center;
            padding: 8px 12px;
        }
        .nav-pills .nav-link.active {
            background-color: #009146;
            color: #fff;
        }
        .btn-custom {
            background-color: #009146;
            color: #fff;
            font-weight: 600;
            padding: 11px;
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
        .forgot-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="login-container">
        <div class="login-header">
            <a href="index.html"><img src="images/logo.png" alt="Anjuman Logo" onerror="this.style.display='none'"></a>
            <h3>Member Login</h3>
            <p>Access your Anjuman Eraquee Membership Portal</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger font-weight-bold" style="font-size: 13px;">
                <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success font-weight-bold" style="font-size: 13px;">
                <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <ul class="nav nav-pills mb-3 justify-content-center" id="pills-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'password' ? 'active' : ''; ?>" id="tab-pass-btn" href="javascript:void(0)" onclick="switchTab('password')">
                    <i class="fa fa-key"></i> Password Login
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'otp' ? 'active' : ''; ?>" id="tab-otp-btn" href="javascript:void(0)" onclick="switchTab('otp')">
                    <i class="fa fa-mobile"></i> OTP Login
                </a>
            </li>
        </ul>

        <!-- Tab 1: Password Login -->
        <div id="pane-pass" style="<?php echo $activeTab === 'password' ? 'display:block;' : 'display:none;'; ?>">
            <form action="user-login.php" method="POST">
                <input type="hidden" name="action" value="login_password">

                <div class="form-group">
                    <label for="login_id" class="font-weight-bold" style="font-size: 13px;">Mobile Number or Email Address</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                        </div>
                        <input type="text" name="login_id" id="login_id" class="form-control" placeholder="Mobile number or email" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="password" class="font-weight-bold" style="font-size: 13px; margin:0;">Password</label>
                        <a href="javascript:void(0)" onclick="toggleForgotBox()" style="font-size: 12px; color: #009146;">Forgot Password?</a>
                    </div>
                    <div class="input-group mt-1">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-lock"></i></span>
                        </div>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-custom mt-2">
                    <i class="fa fa-sign-in"></i> Log In
                </button>
            </form>

            <!-- Collapsible Forgot Password Request -->
            <div id="forgot_box" class="forgot-box" style="display: none;">
                <h6 style="color: #009146; font-weight: 700; font-size: 13px; margin-bottom: 6px;">
                    <i class="fa fa-question-circle"></i> Request Temporary Password
                </h6>
                <p style="font-size: 12px; color: #64748b; margin-bottom: 10px;">
                    Submit your Mobile or Email below. Admin will receive your request and issue a temporary password for you.
                </p>
                <form action="user-login.php" method="POST">
                    <input type="hidden" name="action" value="request_reset">
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" name="reset_id" class="form-control" placeholder="Registered Mobile or Email" required>
                        <div class="input-group-append">
                            <button class="btn btn-success" type="submit" style="background:#009146; font-weight:600;">Request</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab 2: OTP Login -->
        <div id="pane-otp" style="<?php echo $activeTab === 'otp' ? 'display:block;' : 'display:none;'; ?>">
            <form action="user-login.php" method="POST">
                <input type="hidden" name="action" value="send_otp">

                <div class="form-group">
                    <label for="phonenumber" class="font-weight-bold" style="font-size: 13px;">Registered Mobile Number</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-phone"></i></span>
                        </div>
                        <input type="text" name="phonenumber" id="phonenumber" class="form-control" placeholder="Enter 10-digit mobile number">
                    </div>
                </div>

                <button type="submit" class="btn btn-custom mt-2">
                    <i class="fa fa-paper-plane"></i> Send OTP
                </button>
            </form>
        </div>

        <div class="footer-link">
            Not registered yet? <a href="registration.html">Join Membership</a><br>
            <a href="index.html" class="text-muted mt-2 d-inline-block"><i class="fa fa-arrow-left"></i> Back to Homepage</a>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    if (tab === 'password') {
        document.getElementById('pane-pass').style.display = 'block';
        document.getElementById('pane-otp').style.display = 'none';
        document.getElementById('tab-pass-btn').classList.add('active');
        document.getElementById('tab-otp-btn').classList.remove('active');
    } else {
        document.getElementById('pane-pass').style.display = 'none';
        document.getElementById('pane-otp').style.display = 'block';
        document.getElementById('tab-pass-btn').classList.remove('active');
        document.getElementById('tab-otp-btn').classList.add('active');
    }
}

function toggleForgotBox() {
    var box = document.getElementById('forgot_box');
    box.style.display = (box.style.display === 'none' || box.style.display === '') ? 'block' : 'none';
}
</script>

</body>
</html>