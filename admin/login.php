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
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/meanmenu.min.css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="icon" href="../images/icon/tabicon.jpeg" type="image/gif">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Droid Sans', sans-serif;
        }
        .admin-login-wrapper {
            min-height: calc(100vh - 180px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 15px;
        }
        .login-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-top: 4px solid #009146;
            padding: 35px 30px;
        }
    </style>
</head>
<body>
    <!--Header area start here-->
    <header>
        <div class="topbar hidden-sm-down">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-md-9 col-sm-12 col-xs-12">
                        <div class="header-event">
                            <ul class="list-inline count-list">
                                <li><a href="tel:9006297386"><i class="fa fa-mobile"></i> +91 9006297386</a></li>
                                <li><a href="tel:9472502044"><i class="fa fa-mobile"></i> +91 9472502044</a></li>
                                <li><a href="tel:9738455404"><i class="fa fa-mobile"></i> +91 9738455404</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-3 col-sm-12 col-xs-12">
                        <div class="header-social text-right">
                            <ul class="list-inline">
                                <li><a href="../registration.html">Join Membership |</a></li>
                                <li><a href="../user-login.php">User Login |</a></li>
                                <li><a href="login.php">Admin Login</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="main-header hidden-sm-down" id="sticky">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                        <div class="logo-area">
                            <a href="../index.html"><img src="../images/logo/logo.png" alt="" /></a>
                        </div>
                    </div>
                    <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
                        <div class="menu-area">
                            <nav>
                                <ul class="list-inline">
                                    <li><a href="../index.html">Home</a></li>
                                    <li><a href="../about.html">About Us</a></li>
                                    <li class="drop-menu">
                                        <a href="javascript:void(0)">Personalities</a>
                                        <ul class="down-menu">
                                            <li><a href="../index.html">Freedom Fighter</a></li>
                                            <li><a href="../index.html">Bureaucrat</a></li>
                                            <li><a href="../index.html">Politicians</a></li>
                                            <li><a href="../index.html">Doctors</a></li>
                                        </ul>
                                    </li>
                                    <li class="drop-menu">
                                        <a href="javascript:void(0)">Teams</a>
                                        <ul class="down-menu">
                                            <li><a href="../index.html">Core Executive Members</a></li>
                                            <li><a href="../index.html">State Level</a></li>
                                            <li><a href="../districtlevel.html">District Level</a></li>
                                            <li><a href="../index.html">Block Level</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="../about.html">Mission</a></li>
                                    <li class="drop-menu">
                                        <a href="javascript:void(0)">Gallery</a>
                                        <ul class="down-menu">
                                            <li><a href="../gallery-col-4.html">Photos</a></li>
                                            <li><a href="../index.html">Videos</a></li>
                                        </ul>
                                    </li>
                                    <li class="drop-menu">
                                        <a href="javascript:void(0)">Matrimonial</a>
                                        <ul class="down-menu">
                                            <li><a href="../matrimonialregistration.html">Registration</a></li>
                                            <li><a href="../matrimonialregistration.html">Dulha</a></li>
                                            <li><a href="../matrimonialregistration.html">Dulhan</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="../contact.html">Contact Us</a></li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mobile-menu-area">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mobile-menu">
                            <nav id="dropdown">
                                <ul class="list-inline">
                                    <li><a href="../index.html">Home</a></li>
                                    <li><a href="../about.html">About Us</a></li>
                                    <li class="drop-menu">
                                        <a href="javascript:void(0)">Personalities</a>
                                        <ul class="down-menu">
                                            <li><a href="../index.html">Freedom Fighter</a></li>
                                            <li><a href="../index.html">Bureaucrat</a></li>
                                            <li><a href="../index.html">Politicians</a></li>
                                            <li><a href="../index.html">Doctors</a></li>
                                        </ul>
                                    </li>
                                    <li class="drop-menu">
                                        <a href="javascript:void(0)">Teams</a>
                                        <ul class="down-menu">
                                            <li><a href="../coreexecutive.html">Core Executive Members</a></li>
                                            <li><a href="../statelevel.html">State Level</a></li>
                                            <li><a href="../districtlevel.html">District Level</a></li>
                                            <li><a href="../blocklevel.html">Block Level</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="../about.html">Mission</a></li>
                                    <li class="drop-menu">
                                        <a href="javascript:void(0)">Gallery</a>
                                        <ul class="down-menu">
                                            <li><a href="../gallery-col-4.html">Photos</a></li>
                                            <li><a href="../index.html">Videos</a></li>
                                        </ul>
                                    </li>
                                    <li class="drop-menu">
                                        <a href="javascript:void(0)">Matrimonial</a>
                                        <ul class="down-menu">
                                            <li><a href="../matrimonialregistration.html">Registration</a></li>
                                            <li><a href="../matrimonialregistration.html">Dulha</a></li>
                                            <li><a href="../matrimonialregistration.html">Dulhan</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="../contact.html">Contact Us</a></li>
                                    <li><a href="../registration.html">Join Membership</a></li>
                                    <li><a href="../user-login.php">User Login</a></li>
                                    <li><a href="login.php">Admin Login</a></li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!--Header area end here-->

    <div class="admin-login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <h2 style="color: #009146;">Anjuman <span style="color: #38bdf8;">Eraquee</span></h2>
                <p>Super Admin Panel Login</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="padding: 10px 15px; border-radius: 4px; margin-bottom: 20px; font-size: 13px;"><?php echo htmlspecialchars($error); ?></div>
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

                <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; font-size: 16px; border-radius: 4px; border: none; cursor: pointer;">Log In to Dashboard</button>
            </form>

            <div style="text-align: center; margin-top: 24px;">
                <a href="../index.html" style="color: #64748b; font-size: 14px; text-decoration: none;">&larr; Back to Main Website</a>
            </div>
        </div>
    </div>

    <script src="../js/vendor/jquery-1.12.0.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/jquery.meanmenu.js"></script>
    <script>
        jQuery(document).ready(function($) {
            if ($('nav#dropdown').length) {
                $('nav#dropdown').meanmenu();
            }
        });
    </script>
</body>
</html>
