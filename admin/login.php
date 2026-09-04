<?php
session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Default requested credentials: Admin / Admin
    if ($username === 'Admin' && $password === 'Admin') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = 'Admin';
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid Username or Password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Anjuman Eraquee INDIA</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="icon" href="../images/icon/tabicon.jpeg" type="image/gif">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="login-header">
            <h2 style="color: #009146;">Anjuman <span style="color: #38bdf8;">Eraquee</span></h2>
            <p>Super Admin Panel Login</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Enter username (Admin)" required autofocus>
            </div>

            <div class="form-group" style="margin-bottom: 28px;">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter password (Admin)" required>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; font-size: 16px;">Log In to Dashboard</button>
        </form>

        <div style="text-align: center; margin-top: 24px;">
            <a href="../index.html" style="color: #64748b; font-size: 14px; text-decoration: none;">&larr; Back to Main Website</a>
        </div>
    </div>
</body>
</html>

