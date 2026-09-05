<?php
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || !isset($_SESSION['user_id'])) {
    header('Location: user-login.php');
    exit;
}

require_once __DIR__ . '/db.php';
$conn = get_db_connection();

$error = '';
$success = '';
$userId = intval($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else if ($password !== $confirm_password) {
        $error = 'Passwords do not match. Please re-enter.';
    } else if (!$conn) {
        $error = 'Database connection error. Please try again.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $escapedHash = mysqli_real_escape_string($conn, $hash);

        $sql = "UPDATE user_registrtion SET password = '$escapedHash', is_temp_password = 0, reset_requested = 0 WHERE id = $userId";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['password_reset_success'] = 'Your permanent password has been set successfully!';
            header('Location: user-dashboard.php');
            exit;
        } else {
            $error = 'Error updating password: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password | Anjuman Eraquee INDIA</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Droid Sans', sans-serif;
        }
        .reset-container {
            max-width: 440px;
            margin: 70px auto;
            background: #ffffff;
            padding: 35px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-top: 4px solid #009146;
        }
        .reset-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .reset-header h3 {
            font-size: 22px;
            font-weight: 700;
            color: #009146;
            margin: 0;
        }
        .reset-header p {
            color: #666;
            font-size: 13px;
            margin-top: 6px;
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
    </style>
</head>
<body>

<div class="container">
    <div class="reset-container">
        <div class="reset-header">
            <h3><i class="fa fa-key"></i> Set Your Password</h3>
            <p>You have logged in with a temporary password issued by Admin. Please create your permanent password below to continue.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger font-weight-bold" style="font-size: 14px;">
                <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="user-reset-password.php" method="POST" onsubmit="return validateResetForm()">
            <div class="form-group">
                <label for="password" class="font-weight-bold">New Password *</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                    </div>
                    <input type="password" name="password" id="pass_new" class="form-control" placeholder="At least 6 characters" required minlength="6" autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="font-weight-bold">Confirm New Password *</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                    </div>
                    <input type="password" name="confirm_password" id="pass_confirm" class="form-control" placeholder="Re-enter new password" required minlength="6" onkeyup="checkResetMatch()">
                </div>
                <small id="reset_match_msg" style="font-weight:bold; color:red; display:none; margin-top:5px;"></small>
            </div>

            <button type="submit" class="btn btn-custom mt-3">
                <i class="fa fa-check"></i> Save & Continue to Dashboard
            </button>
        </form>
    </div>
</div>

<script>
function checkResetMatch() {
    var p1 = document.getElementById('pass_new').value;
    var p2 = document.getElementById('pass_confirm').value;
    var msg = document.getElementById('reset_match_msg');
    if (p2.length > 0) {
        if (p1 !== p2) {
            msg.style.display = 'block';
            msg.style.color = 'red';
            msg.innerText = 'Passwords do not match!';
            return false;
        } else {
            msg.style.display = 'block';
            msg.style.color = 'green';
            msg.innerText = 'Passwords match!';
            return true;
        }
    } else {
        msg.style.display = 'none';
        return false;
    }
}

function validateResetForm() {
    var p1 = document.getElementById('pass_new').value;
    var p2 = document.getElementById('pass_confirm').value;
    if (p1.length < 6) {
        alert("Password must be at least 6 characters.");
        return false;
    }
    if (p1 !== p2) {
        alert("Passwords do not match. Please re-enter.");
        return false;
    }
    return true;
}
</script>

</body>
</html>