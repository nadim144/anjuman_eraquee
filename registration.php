<?php
session_start();

require_once __DIR__ . '/db.php';
$conn = get_db_connection();

$isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && !empty($_SESSION['user_id']);
$userData = [];

if ($isLoggedIn && $conn) {
    $uid = intval($_SESSION['user_id']);
    $res = mysqli_query($conn, "SELECT * FROM user_registrtion WHERE id = $uid LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $userData = mysqli_fetch_assoc($res);
    }
}
?>
<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="icon" href="images/icon/tabicon.jpeg" type="image/gif" sizes="30x25">
    <title>Membership Registration | Anjuman Eraquee INDIA</title>
    <meta name="description" content="Anjuman Eraquee INDIA Membership Registration">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="apple-touch-icon.png">

    <!-- all css here -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/jquery-ui.min.css">
    <link rel="stylesheet" href="css/meanmenu.min.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/bxslider.css">
    <link rel="stylesheet" href="css/magnific-popup.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <script src="js/vendor/modernizr-2.8.3.min.js"></script>

    <style>
        .registration-page-wrapper {
            clear: both;
            background: #f8fafc;
            padding: 40px 0 80px 0;
            min-height: 80vh;
        }

        /* Step Wizard Navigation */
        .step-wizard {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            position: relative;
            background: #ffffff;
            padding: 20px 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
        }

        .step-wizard::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50px;
            right: 50px;
            height: 3px;
            background: #e2e8f0;
            z-index: 1;
            transform: translateY(-50%);
        }

        .step-item {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #ffffff;
            padding: 0 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .step-badge {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 8px;
            border: 2px solid #cbd5e1;
            transition: all 0.3s ease;
        }

        .step-title {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            text-align: center;
        }

        .step-item.active .step-badge {
            background: #009146;
            color: #ffffff;
            border-color: #009146;
            box-shadow: 0 0 0 4px rgba(0, 145, 70, 0.18);
        }

        .step-item.active .step-title {
            color: #009146;
            font-weight: 700;
        }

        .step-item.completed .step-badge {
            background: #10b981;
            color: #ffffff;
            border-color: #10b981;
        }

        /* Form Card */
        .reg-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 35px 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            border-top: 4px solid #009146;
        }

        .reg-section-title {
            color: #009146;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 25px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .reg-section-title i {
            color: #38bdf8;
        }

        .form-label-custom {
            font-weight: 600;
            color: #334155;
            font-size: 13px;
            margin-bottom: 6px;
            display: block;
        }

        .form-control-custom {
            width: 100%;
            height: 44px;
            padding: 10px 14px;
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            color: #1e293b;
            background: #ffffff;
            transition: border-color 0.2s;
            margin-bottom: 18px;
        }

        .form-control-custom:focus {
            border-color: #009146;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 145, 70, 0.12);
        }

        textarea.form-control-custom {
            height: 100px;
            resize: vertical;
        }

        /* Radio Buttons */
        .custom-radio-group {
            display: flex;
            gap: 15px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .custom-radio-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
        }

        .custom-radio-item input[type="radio"] {
            margin: 0;
            cursor: pointer;
        }

        /* Password with Eye icon */
        .password-input-group {
            position: relative;
            margin-bottom: 18px;
        }

        .password-input-group input {
            padding-right: 44px;
            margin-bottom: 0;
        }

        .btn-eye-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #64748b;
            font-size: 16px;
            padding: 4px;
            outline: none;
        }

        .btn-eye-toggle:hover {
            color: #009146;
        }

        /* Action Buttons */
        .wizard-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn-action {
            font-size: 14px;
            font-weight: 600;
            padding: 11px 24px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }

        .btn-action-prev {
            background: #e2e8f0;
            color: #475569;
        }

        .btn-action-prev:hover {
            background: #cbd5e1;
            color: #1e293b;
        }

        .btn-action-save {
            background: #f0fdf4;
            color: #009146;
            border: 1.5px solid #009146;
        }

        .btn-action-save:hover {
            background: #009146;
            color: #ffffff;
        }

        .btn-action-next {
            background: #009146;
            color: #ffffff;
        }

        .btn-action-next:hover {
            background: #007a3a;
            color: #ffffff;
        }

        .btn-action-submit {
            background: #0284c7;
            color: #ffffff;
        }

        .btn-action-submit:hover {
            background: #0369a1;
            color: #ffffff;
        }

        /* Alert Toast */
        #status_banner {
            display: none;
            padding: 12px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        /* Sign-up Card for Unauthenticated View */
        .signup-promo-card {
            max-width: 520px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            padding: 35px 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border-top: 4px solid #009146;
        }

        .signup-promo-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .signup-promo-header img {
            max-height: 55px;
            margin-bottom: 12px;
        }

        .signup-promo-header h3 {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 6px 0;
        }

        .signup-promo-header p {
            color: #64748b;
            font-size: 13px;
            margin: 0;
        }

        /* Mobile specific adjustments */
        @media (max-width: 767px) {
            .registration-page-wrapper {
                padding: 20px 10px 50px 10px;
            }
            .step-wizard {
                padding: 15px 10px;
            }
            .step-title {
                font-size: 11px;
            }
            .step-badge {
                width: 36px;
                height: 36px;
                font-size: 14px;
            }
            .reg-card {
                padding: 22px 16px;
            }
            .wizard-actions {
                flex-direction: column;
                width: 100%;
            }
            .wizard-actions > div {
                width: 100%;
                display: flex;
                gap: 10px;
            }
            .wizard-actions .btn-action {
                flex: 1;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <!--Header area start here-->
    <header>
        <div class="topbar hidden-sm-down">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="header-event">
                            <ul class="list-inline count-list">
                                <li><a href="tel:9006297386"><i class="fa fa-mobile"></i> +91 9006297386</a></li>
                                <li><a href="tel:9472502044"><i class="fa fa-mobile"></i> +91 9472502044</a></li>
                                <li><a href="tel:9738455404"><i class="fa fa-mobile"></i> +91 9738455404</a></li>
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
                            <a href="index.html"><img src="images/logo/logo.png" alt="Anjuman Eraquee INDIA" /></a>
                        </div>
                    </div>
                    <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
                        <div class="menu-area">
                            <nav>
                                <ul class="list-inline">
                                    <li><a href="index.html">Home</a></li>
                                    <li><a href="about.html">About Us</a></li>
                                    <li class="drop-menu">
                                        <a href="javascript:void(0)">Personalities</a>
                                        <ul class="down-menu">
                                            <li><a href="index.html">Freedom Fighter</a></li>
                                            <li><a href="index.html">Bureaucrat</a></li>
                                            <li><a href="index.html">Politicians</a></li>
                                            <li><a href="index.html">Doctors</a></li>
                                        </ul>
                                    </li>
                                    <li class="drop-menu">
                                        <a href="javascript:void(0)">Teams</a>
                                        <ul class="down-menu">
                                            <li><a href="coreexecutive.html">Core Executive Members</a></li>
                                            <li><a href="statelevel.html">State Level</a></li>
                                            <li><a href="districtlevel.html">District Level</a></li>
                                            <li><a href="blocklevel.html">Block Level</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="about.html">Mission</a></li>
                                    <li class="drop-menu">
                                        <a href="javascript:void(0)">Gallery</a>
                                        <ul class="down-menu">
                                            <li><a href="gallery-col-4.html">Photos</a></li>
                                            <li><a href="index.html">Videos</a></li>
                                        </ul>
                                    </li>
                                    <li class="drop-menu">
                                        <a href="javascript:void(0)">Matrimonial</a>
                                        <ul class="down-menu">
                                            <li><a href="matrimonialregistration.html">Registration</a></li>
                                            <li><a href="matrimonialregistration.html">Dulha</a></li>
                                            <li><a href="matrimonialregistration.html">Dulhan</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="contact.html">Contact Us</a></li>
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
                                    <li><a href="index.html">Home</a></li>
                                    <li><a href="about.html">About Us</a></li>
                                    <li class="drop-menu">
                                        <a href="javascript:void(0)">Personalities</a>
                                        <ul class="down-menu">
                                            <li><a href="index.html">Freedom Fighter</a></li>
                                            <li><a href="index.html">Bureaucrat</a></li>
                                            <li><a href="index.html">Politicians</a></li>
                                            <li><a href="index.html">Doctors</a></li>
                                        </ul>
                                    </li>
                                    <li class="drop-menu">
                                        <a href="javascript:void(0)">Teams</a>
                                        <ul class="down-menu">
                                            <li><a href="coreexecutive.html">Core Executive Members</a></li>
                                            <li><a href="statelevel.html">State Level</a></li>
                                            <li><a href="districtlevel.html">District Level</a></li>
                                            <li><a href="blocklevel.html">Block Level</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="about.html">Mission</a></li>
                                    <li class="drop-menu">
                                        <a href="javascript:void(0)">Gallery</a>
                                        <ul class="down-menu">
                                            <li><a href="gallery-col-4.html">Photos</a></li>
                                            <li><a href="index.html">Videos</a></li>
                                        </ul>
                                    </li>
                                    <li class="drop-menu">
                                        <a href="javascript:void(0)">Matrimonial</a>
                                        <ul class="down-menu">
                                            <li><a href="matrimonialregistration.html">Registration</a></li>
                                            <li><a href="matrimonialregistration.html">Dulha</a></li>
                                            <li><a href="matrimonialregistration.html">Dulhan</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="contact.html">Contact Us</a></li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!--Header area end here-->

    <div class="registration-page-wrapper">
        <div class="container">

            <?php if (!$isLoggedIn): ?>
                <!-- Not Logged In: Step 0 / Quick Sign-Up Dialog View -->
                <div class="signup-promo-card">
                    <div class="signup-promo-header">
                        <a href="index.html"><img src="images/logo/logo.png" alt="Anjuman Eraquee INDIA"></a>
                        <h3>Join Membership</h3>
                        <p>Create your credentials to start your membership application</p>
                    </div>

                    <div id="signup_alert" style="display:none;" class="alert alert-danger font-weight-bold" style="font-size:13px;"></div>

                    <form id="signup_form" onsubmit="handleInitialSignUp(event)">
                        <div class="form-group">
                            <label class="form-label-custom" for="su_phone">Registered Mobile Number *</label>
                            <input type="text" id="su_phone" name="phonenumber" class="form-control-custom" placeholder="Enter 10-digit mobile number" maxlength="10" required autofocus>
                        </div>

                        <div class="form-group">
                            <label class="form-label-custom" for="su_email">Email Address *</label>
                            <input type="email" id="su_email" name="email" class="form-control-custom" placeholder="name@example.com" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label-custom" for="su_pass">Create Password *</label>
                            <div class="password-input-group">
                                <input type="password" id="su_pass" name="password" class="form-control-custom" placeholder="Minimum 6 characters" minlength="6" required>
                                <button type="button" class="btn-eye-toggle" onclick="togglePasswordVisibility('su_pass', 'su_pass_icon')">
                                    <i id="su_pass_icon" class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label-custom" for="su_confirm_pass">Confirm Password *</label>
                            <div class="password-input-group">
                                <input type="password" id="su_confirm_pass" name="confirm_password" class="form-control-custom" placeholder="Re-enter password" minlength="6" required onkeyup="checkInitialPasswordMatch()">
                                <button type="button" class="btn-eye-toggle" onclick="togglePasswordVisibility('su_confirm_pass', 'su_confirm_pass_icon')">
                                    <i id="su_confirm_pass_icon" class="fa fa-eye"></i>
                                </button>
                            </div>
                            <small id="su_match_msg" style="display:none; font-weight:600; margin-top:4px;"></small>
                        </div>

                        <button type="submit" id="su_submit_btn" class="btn-action btn-action-next w-100 text-center" style="width:100%; justify-content:center; margin-top:10px;">
                            <span>Register & Continue</span> <i class="fa fa-arrow-right"></i>
                        </button>

                        <div class="text-center mt-3" style="margin-top:20px; font-size:13px; color:#64748b;">
                            Already have an account? <a href="user-login.php" style="color:#009146; font-weight:700;">Log In here</a>
                        </div>
                    </form>
                </div>

            <?php else: ?>

                <!-- Logged In: 3-Step Progressive Registration Wizard -->
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-md-12">

                        <!-- User Welcome Header -->
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="margin-bottom: 20px;">
                            <div>
                                <h3 style="font-weight: 700; color: #1e293b; margin: 0;">Membership Application</h3>
                                <p style="color: #64748b; font-size: 13px; margin: 0;">
                                    Mobile: +91 <?php echo htmlspecialchars($userData['phonenumber'] ?? $_SESSION['user_phone'] ?? ''); ?> | 
                                    Email: <?php echo htmlspecialchars($userData['email'] ?? $_SESSION['user_email'] ?? ''); ?>
                                </p>
                            </div>
                            <div style="margin-top: 5px;">
                                <a href="user-dashboard.php" class="btn btn-sm btn-outline-secondary" style="font-weight:600;"><i class="fa fa-dashboard"></i> View Dashboard</a>
                                <a href="user-logout.php" class="btn btn-sm btn-danger" style="font-weight:600;"><i class="fa fa-sign-out"></i> Logout</a>
                            </div>
                        </div>

                        <!-- Step Wizard Tabs -->
                        <div class="step-wizard">
                            <div class="step-item active" id="step_tab_1" onclick="goToStep(1)">
                                <div class="step-badge"><span id="badge_icon_1">1</span></div>
                                <div class="step-title">1. Personal Details</div>
                            </div>
                            <div class="step-item" id="step_tab_2" onclick="goToStep(2)">
                                <div class="step-badge"><span id="badge_icon_2">2</span></div>
                                <div class="step-title">2. Address Details</div>
                            </div>
                            <div class="step-item" id="step_tab_3" onclick="goToStep(3)">
                                <div class="step-badge"><span id="badge_icon_3">3</span></div>
                                <div class="step-title">3. Education & Profession</div>
                            </div>
                        </div>

                        <!-- Status Toast Alert -->
                        <div id="status_banner" class="alert"></div>

                        <!-- Form Container -->
                        <div class="reg-card">

                            <!-- PART 1: PERSONAL DETAILS -->
                            <div id="step_part_1">
                                <div class="reg-section-title">
                                    <i class="fa fa-user-circle"></i> Part 1: Personal Details
                                </div>
                                <form id="form_step_1" enctype="multipart/form-data">
                                    <!-- Profile Picture Upload Widget -->
                                    <div class="row align-items-center mb-4" style="background:#f8fafc; padding:18px 20px; border-radius:10px; border:1.5px dashed #cbd5e1; margin-bottom: 25px;">
                                        <div class="col-sm-3 col-xs-12 text-center mb-2">
                                            <div style="position:relative; display:inline-block;">
                                                <?php 
                                                $picSrc = !empty($userData['profile_picture']) ? htmlspecialchars($userData['profile_picture']) : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="%2394a3b8"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>';
                                                ?>
                                                <img id="avatar_preview" src="<?php echo $picSrc; ?>" 
                                                     alt="Profile Picture" 
                                                     style="width: 105px; height: 105px; border-radius: 50%; object-fit: cover; border: 3px solid #009146; box-shadow: 0 4px 12px rgba(0,0,0,0.1); background:#ffffff;">
                                                <label for="profile_pic_input" title="Change Photo" style="position:absolute; bottom:2px; right:2px; background:#009146; color:#ffffff; border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 6px rgba(0,0,0,0.25); border: 2px solid #ffffff; margin:0;">
                                                    <i class="fa fa-camera"></i>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-9 col-xs-12">
                                            <label class="form-label-custom" style="font-size:15px; margin-bottom:4px; color:#1e293b;">
                                                <i class="fa fa-picture-o text-success"></i> Profile Picture
                                            </label>
                                            <p style="font-size:12px; color:#64748b; margin-bottom:10px;">
                                                Upload your passport-style photograph (JPG, PNG, or WEBP, max 5MB). This photo will be printed on your official Membership Certificate.
                                            </p>
                                            <input type="file" name="profile_picture" id="profile_pic_input" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="previewProfilePicture(this)">
                                            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="document.getElementById('profile_pic_input').click()" style="font-weight:600; padding:6px 18px; border-radius:6px; border:1.5px solid #009146; color:#009146;">
                                                    <i class="fa fa-upload"></i> Choose Photo
                                                </button>
                                                <span id="pic_name_display" style="font-size:12px; color:#475569; font-weight:600;"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12">
                                            <label class="form-label-custom">Full Name *</label>
                                            <input type="text" name="FullName" id="p1_fullname" class="form-control-custom" placeholder="Enter Full Name" value="<?php echo htmlspecialchars($userData['username'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-sm-6 col-xs-12">
                                            <label class="form-label-custom">Father's Name *</label>
                                            <input type="text" name="fathername" id="p1_fathername" class="form-control-custom" placeholder="Father's Name" value="<?php echo htmlspecialchars($userData['fathername'] ?? ''); ?>" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12">
                                            <label class="form-label-custom">Mother's Name *</label>
                                            <input type="text" name="mothername" id="p1_mothername" class="form-control-custom" placeholder="Mother's Name" value="<?php echo htmlspecialchars($userData['mothername'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-sm-6 col-xs-12">
                                            <label class="form-label-custom">Grandfather's Name (Dada) *</label>
                                            <input type="text" name="grandfathername" id="p1_grandfathername" class="form-control-custom" placeholder="Grandfather's Name" value="<?php echo htmlspecialchars($userData['grandfathername'] ?? ''); ?>" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-4 col-xs-12">
                                            <label class="form-label-custom">Date of Birth *</label>
                                            <input type="date" name="dob" id="p1_dob" class="form-control-custom" value="<?php echo htmlspecialchars($userData['dob'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-sm-4 col-xs-12">
                                            <label class="form-label-custom">Gender *</label>
                                            <select name="gender" id="p1_gender" class="form-control-custom" required style="cursor:pointer;">
                                                <option value="">-- Select Gender --</option>
                                                <option value="male" <?php echo (($userData['gender'] ?? 'male') === 'male') ? 'selected' : ''; ?>>Male</option>
                                                <option value="female" <?php echo (($userData['gender'] ?? '') === 'female') ? 'selected' : ''; ?>>Female</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4 col-xs-12">
                                            <label class="form-label-custom">Marital Status *</label>
                                            <select name="maritalstatus" id="p1_maritalstatus" class="form-control-custom" required style="cursor:pointer;">
                                                <option value="">-- Select Marital Status --</option>
                                                <option value="married" <?php echo (($userData['maritalstatus'] ?? 'married') === 'married') ? 'selected' : ''; ?>>Married</option>
                                                <option value="unmarried" <?php echo (($userData['maritalstatus'] ?? '') === 'unmarried') ? 'selected' : ''; ?>>Unmarried</option>
                                                <option value="divorced" <?php echo (($userData['maritalstatus'] ?? '') === 'divorced') ? 'selected' : ''; ?>>Divorced</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12">
                                            <label class="form-label-custom">Aadhaar Card Number *</label>
                                            <div style="position:relative;">
                                                <?php
                                                $formattedAadhaar = '';
                                                if (!empty($userData['aadhaar_number'])) {
                                                    $digitsOnly = preg_replace('/\D/', '', $userData['aadhaar_number']);
                                                    $formattedAadhaar = trim(chunk_split($digitsOnly, 4, ' '));
                                                }
                                                ?>
                                                <input type="text" name="aadhaar_number" id="p1_aadhaar" class="form-control-custom" placeholder="XXXX XXXX XXXX (12 digits)" maxlength="14" value="<?php echo htmlspecialchars($formattedAadhaar); ?>" required oninput="formatAndValidateAadhaar(this)">
                                                <span id="aadhaar_status_icon" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:16px;"></span>
                                            </div>
                                            <small id="aadhaar_msg" style="display:none; font-weight:600; margin-top:-12px; margin-bottom:14px;"></small>
                                        </div>
                                        <div class="col-sm-6 col-xs-12">
                                            <label class="form-label-custom">Additional Mobile Number</label>
                                            <div style="position:relative;">
                                                <input type="text" name="additional_mobile" id="p1_add_mobile" class="form-control-custom" placeholder="10-digit mobile number" maxlength="10" value="<?php echo htmlspecialchars($userData['additional_mobile'] ?? ''); ?>" oninput="validateAdditionalMobile(this)">
                                                <span id="add_mobile_status_icon" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:16px;"></span>
                                            </div>
                                            <small id="add_mobile_msg" style="display:none; font-weight:600; margin-top:-12px; margin-bottom:14px;"></small>
                                        </div>
                                    </div>

                                    <div class="wizard-actions">
                                        <div></div>
                                        <div style="display:flex; gap:10px;">
                                            <button type="button" class="btn-action btn-action-save" onclick="saveProgressiveStep(1, false)">
                                                <i class="fa fa-floppy-o"></i> <span>Save</span>
                                            </button>
                                            <button type="button" class="btn-action btn-action-next" onclick="saveProgressiveStep(1, true)">
                                                <span>Next: Address</span> <i class="fa fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- PART 2: ADDRESS DETAILS -->
                            <div id="step_part_2" style="display:none;">
                                <div class="reg-section-title">
                                    <i class="fa fa-home"></i> Part 2: Current & Permanent Address
                                </div>
                                <form id="form_step_2">
                                    <h5 style="color:#334155; font-size:15px; font-weight:700; margin-bottom:15px; border-left:3px solid #009146; padding-left:8px;">
                                        Present / Current Address
                                    </h5>
                                    <div class="row">
                                        <div class="col-sm-4 col-xs-12">
                                            <label class="form-label-custom">Present Address (House/Street) *</label>
                                            <input type="text" name="presentaddress" id="p2_pres_addr" class="form-control-custom" placeholder="House No / Street" value="<?php echo htmlspecialchars($userData['presentaddress'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-sm-4 col-xs-12">
                                            <label class="form-label-custom">Village / At + Post *</label>
                                            <input type="text" name="presentvillatpost" id="p2_pres_vill" class="form-control-custom" placeholder="Village / Post Office" value="<?php echo htmlspecialchars($userData['presentvillatpost'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-sm-4 col-xs-12">
                                            <label class="form-label-custom">District *</label>
                                            <input type="text" name="presentdistrict" id="p2_pres_dist" class="form-control-custom" placeholder="District" value="<?php echo htmlspecialchars($userData['presentdistrict'] ?? ''); ?>" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-4 col-xs-12">
                                            <label class="form-label-custom">Pin Code *</label>
                                            <input type="text" name="presentpincode" id="p2_pres_pin" class="form-control-custom" placeholder="6-digit Pincode" value="<?php echo htmlspecialchars($userData['presentpincode'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-sm-4 col-xs-12">
                                            <label class="form-label-custom">State *</label>
                                            <input type="text" name="presentstate" id="p2_pres_state" class="form-control-custom" placeholder="State" value="<?php echo htmlspecialchars($userData['presentstate'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-sm-4 col-xs-12">
                                            <label class="form-label-custom">Country *</label>
                                            <input type="text" name="presentcountry" id="p2_pres_country" class="form-control-custom" placeholder="Country" value="<?php echo htmlspecialchars($userData['presentcountry'] ?? 'India'); ?>" required>
                                        </div>
                                    </div>

                                    <!-- Same Address Checkbox -->
                                    <div class="form-group" style="background:#f1f5f9; padding:12px 16px; border-radius:6px; margin: 15px 0 25px 0;">
                                        <label style="cursor:pointer; margin:0; display:flex; align-items:center; gap:8px; font-weight:600; color:#1e293b;">
                                            <input type="checkbox" id="same_as_present" name="presentaddresstopermanent" value="1" <?php echo !empty($userData['presentaddresstopermanent']) ? 'checked' : ''; ?> onchange="handleSameAddressToggle()">
                                            <span>Permanent address is the same as Present address</span>
                                        </label>
                                    </div>

                                    <!-- Permanent Address -->
                                    <div id="permanent_address_wrapper" style="<?php echo !empty($userData['presentaddresstopermanent']) ? 'display:none;' : ''; ?>">
                                        <h5 style="color:#334155; font-size:15px; font-weight:700; margin-bottom:15px; border-left:3px solid #38bdf8; padding-left:8px;">
                                            Permanent Address
                                        </h5>
                                        <div class="row">
                                            <div class="col-sm-4 col-xs-12">
                                                <label class="form-label-custom">Permanent Address *</label>
                                                <input type="text" name="permanentaddress" id="p2_perm_addr" class="form-control-custom" placeholder="House No / Street" value="<?php echo htmlspecialchars($userData['permanentaddress'] ?? ''); ?>">
                                            </div>
                                            <div class="col-sm-4 col-xs-12">
                                                <label class="form-label-custom">Village / At + Post *</label>
                                                <input type="text" name="permanentvillatpost" id="p2_perm_vill" class="form-control-custom" placeholder="Village / Post Office" value="<?php echo htmlspecialchars($userData['permanentvillatpost'] ?? ''); ?>">
                                            </div>
                                            <div class="col-sm-4 col-xs-12">
                                                <label class="form-label-custom">District *</label>
                                                <input type="text" name="permanentdistrict" id="p2_perm_dist" class="form-control-custom" placeholder="District" value="<?php echo htmlspecialchars($userData['permanentdistrict'] ?? ''); ?>">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-4 col-xs-12">
                                                <label class="form-label-custom">Pin Code *</label>
                                                <input type="text" name="permanentpincode" id="p2_perm_pin" class="form-control-custom" placeholder="6-digit Pincode" value="<?php echo htmlspecialchars($userData['permanentpincode'] ?? ''); ?>">
                                            </div>
                                            <div class="col-sm-4 col-xs-12">
                                                <label class="form-label-custom">State *</label>
                                                <input type="text" name="permanentstate" id="p2_perm_state" class="form-control-custom" placeholder="State" value="<?php echo htmlspecialchars($userData['permanentstate'] ?? ''); ?>">
                                            </div>
                                            <div class="col-sm-4 col-xs-12">
                                                <label class="form-label-custom">Country *</label>
                                                <input type="text" name="permanentcountry" id="p2_perm_country" class="form-control-custom" placeholder="Country" value="<?php echo htmlspecialchars($userData['permanentcountry'] ?? 'India'); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="wizard-actions">
                                        <button type="button" class="btn-action btn-action-prev" onclick="goToStep(1)">
                                            <i class="fa fa-arrow-left"></i> <span>Previous</span>
                                        </button>
                                        <div style="display:flex; gap:10px;">
                                            <button type="button" class="btn-action btn-action-save" onclick="saveProgressiveStep(2, false)">
                                                <i class="fa fa-floppy-o"></i> <span>Save</span>
                                            </button>
                                            <button type="button" class="btn-action btn-action-next" onclick="saveProgressiveStep(2, true)">
                                                <span>Next: Education & Profession</span> <i class="fa fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- PART 3: EDUCATION & PROFESSION -->
                            <div id="step_part_3" style="display:none;">
                                <div class="reg-section-title">
                                    <i class="fa fa-graduation-cap"></i> Part 3: Education & Working Professional Details
                                </div>
                                <form id="form_step_3">
                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12">
                                            <label class="form-label-custom">Highest Qualification *</label>
                                            <select name="qulification" id="p3_qual" class="form-control-custom" required>
                                                <option value="">-- Select Highest Qualification --</option>
                                                <?php
                                                $quals = ['High School 10th', 'Intermediate 12th', 'Graduation', 'Post Graduation', 'M.Phil', 'Ph.D', 'Diploma', 'Other'];
                                                $selectedQual = $userData['qulification'] ?? '';
                                                foreach ($quals as $q) {
                                                    $sel = ($selectedQual === $q) ? 'selected' : '';
                                                    echo "<option value=\"$q\" $sel>$q</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-6 col-xs-12">
                                            <label class="form-label-custom">Qualification Details (Degree / Branch) *</label>
                                            <input type="text" name="qualificationdetails" id="p3_qualdet" class="form-control-custom" placeholder="e.g. B.Tech (CSE), MBBS, B.Com, M.A" value="<?php echo htmlspecialchars($userData['qualificationdetails'] ?? ''); ?>" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12">
                                            <label class="form-label-custom">Current Occupation *</label>
                                            <select name="occupation" id="p3_occ" class="form-control-custom" required>
                                                <option value="">-- Select Current Occupation --</option>
                                                <?php
                                                $occs = ['Business', 'Entrepreneur', 'Private Sector', 'Government Employee', 'Self Employed', 'Student', 'Homemaker', 'Other'];
                                                $selectedOcc = $userData['occupation'] ?? '';
                                                foreach ($occs as $o) {
                                                    $sel = ($selectedOcc === $o) ? 'selected' : '';
                                                    echo "<option value=\"$o\" $sel>$o</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-6 col-xs-12">
                                            <label class="form-label-custom">Occupation Details / Designation *</label>
                                            <input type="text" name="occupationdetails" id="p3_occdet" class="form-control-custom" placeholder="e.g. Software Engineer, Business Owner, Teacher" value="<?php echo htmlspecialchars($userData['occupationdetails'] ?? ''); ?>" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12">
                                            <label class="form-label-custom">WhatsApp Number (Optional)</label>
                                            <input type="text" name="whatsappnumber" id="p3_wa" class="form-control-custom" placeholder="WhatsApp Number" value="<?php echo htmlspecialchars($userData['whatsappnumber'] ?? ''); ?>">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label-custom">Suggestion or Feedback for Anjuman Eraquee (Optional)</label>
                                        <textarea name="messageinfo" id="p3_msg" class="form-control-custom" placeholder="Write your message, feedback or suggestions..."><?php echo htmlspecialchars($userData['messageinfo'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="wizard-actions">
                                        <button type="button" class="btn-action btn-action-prev" onclick="goToStep(2)">
                                            <i class="fa fa-arrow-left"></i> <span>Previous</span>
                                        </button>
                                        <div style="display:flex; gap:10px;">
                                            <button type="button" class="btn-action btn-action-save" onclick="saveProgressiveStep(3, false)">
                                                <i class="fa fa-floppy-o"></i> <span>Save</span>
                                            </button>
                                            <button type="button" class="btn-action btn-action-submit" onclick="submitFinalRegistration()">
                                                <i class="fa fa-check-circle"></i> <span>Submit Registration</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>

    <!--Footer area start here-->
    <footer>
        <div class="footer-top">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
                        <div class="footer-widget">
                            <h4>Contact Us</h4>
                            <div class="about-foo">
                                <p>Anjuman Eraquee INDIA, #3/17, Street No. 2, Jantavihar, Jhangir Puri Village, Mukundpur, Delhi - 110042, INDIA</p>
                                <ul>
                                    <li><span class="ico"><i class="fa fa-phone"></i></span><strong>+91 9006297386</strong></li>
                                    <li><span class="ico"><i class="fa fa-phone"></i></span><strong>+91 9472502044</strong></li>
                                    <li><span class="ico"><i class="fa fa-phone"></i></span><strong>+91 9738455404</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <div class="copyright">
                            <p>© Copyright 2026 by <span>Anjuman Eraquee INDIA</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- all js here -->
    <script src="js/vendor/jquery-1.12.0.min.js"></script>
    <script src="js/tether.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.bxslider.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/jquery.meanmenu.js"></script>
    <script src="js/jarallax.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/site-settings.js"></script>

    <script>
    // Password visibility toggle helper
    function togglePasswordVisibility(fieldId, iconId) {
        var input = document.getElementById(fieldId);
        var icon = document.getElementById(iconId);
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.className = 'fa fa-eye-slash';
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.className = 'fa fa-eye';
            }
        }
    }

    // Check Initial Sign-Up Password match
    function checkInitialPasswordMatch() {
        var p1 = document.getElementById('su_pass');
        var p2 = document.getElementById('su_confirm_pass');
        var msg = document.getElementById('su_match_msg');
        if (!p1 || !p2 || !msg) return;

        if (p2.value.length > 0) {
            msg.style.display = 'block';
            if (p1.value === p2.value) {
                msg.style.color = '#009146';
                msg.innerHTML = '<i class="fa fa-check"></i> Passwords match!';
                return true;
            } else {
                msg.style.color = '#dc2626';
                msg.innerHTML = '<i class="fa fa-times"></i> Passwords do not match!';
                return false;
            }
        } else {
            msg.style.display = 'none';
            return false;
        }
    }

    // Handle Quick Sign-Up submission
    function handleInitialSignUp(e) {
        e.preventDefault();
        var form = document.getElementById('signup_form');
        var alertBox = document.getElementById('signup_alert');
        var btn = document.getElementById('su_submit_btn');

        var phone = document.getElementById('su_phone').value.trim();
        var email = document.getElementById('su_email').value.trim();
        var pass = document.getElementById('su_pass').value;
        var confirmPass = document.getElementById('su_confirm_pass').value;

        if (phone.replace(/[^0-9]/g, '').length < 10) {
            alertBox.className = 'alert alert-danger';
            alertBox.style.display = 'block';
            alertBox.innerHTML = '<i class="fa fa-exclamation-circle"></i> Please enter a valid 10-digit mobile number.';
            return;
        }

        if (pass.length < 6) {
            alertBox.className = 'alert alert-danger';
            alertBox.style.display = 'block';
            alertBox.innerHTML = '<i class="fa fa-exclamation-circle"></i> Password must be at least 6 characters.';
            return;
        }

        if (pass !== confirmPass) {
            alertBox.className = 'alert alert-danger';
            alertBox.style.display = 'block';
            alertBox.innerHTML = '<i class="fa fa-exclamation-circle"></i> Passwords do not match.';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Creating Account...';

        var formData = new FormData(form);

        fetch('signup.php', {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                alertBox.className = 'alert alert-success';
                alertBox.style.display = 'block';
                alertBox.innerHTML = '<i class="fa fa-check-circle"></i> ' + data.message;
                setTimeout(function() {
                    window.location.href = data.redirect || 'registration.php';
                }, 800);
            } else {
                btn.disabled = false;
                btn.innerHTML = '<span>Register & Continue</span> <i class="fa fa-arrow-right"></i>';
                alertBox.className = 'alert alert-danger';
                alertBox.style.display = 'block';
                if (data.already_exists) {
                    alertBox.innerHTML = '<i class="fa fa-info-circle"></i> ' + data.message + ' <a href="user-login.php" style="font-weight:bold; text-decoration:underline;">Click to Log In</a>';
                } else {
                    alertBox.innerHTML = '<i class="fa fa-exclamation-circle"></i> ' + data.message;
                }
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = '<span>Register & Continue</span> <i class="fa fa-arrow-right"></i>';
            alertBox.className = 'alert alert-danger';
            alertBox.style.display = 'block';
            alertBox.innerHTML = '<i class="fa fa-exclamation-circle"></i> An unexpected network error occurred. Please try again.';
        });
    }

    // Preview selected Profile Picture
    function previewProfilePicture(input) {
        if (input.files && input.files[0]) {
            var file = input.files[0];
            if (file.size > 5 * 1024 * 1024) {
                alert('File size exceeds 5MB limit. Please select a smaller photo.');
                input.value = '';
                return;
            }
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatar_preview').src = e.target.result;
            };
            reader.readAsDataURL(file);
            document.getElementById('pic_name_display').textContent = file.name;
        }
    }

    // Format & Validate 12-digit Indian Aadhaar Number
    function formatAndValidateAadhaar(input) {
        if (!input) return false;
        var val = input.value.replace(/\D/g, '');
        if (val.length > 12) val = val.substring(0, 12);

        var formatted = '';
        for (var i = 0; i < val.length; i++) {
            if (i > 0 && i % 4 === 0) formatted += ' ';
            formatted += val[i];
        }
        input.value = formatted;

        var statusIcon = document.getElementById('aadhaar_status_icon');
        var msg = document.getElementById('aadhaar_msg');
        if (!statusIcon || !msg) return true;

        if (val.length === 0) {
            statusIcon.innerHTML = '';
            msg.style.display = 'none';
            input.setCustomValidity('Aadhaar number is required');
            return false;
        }

        var startsWithZeroOrOne = /^[01]/.test(val);
        var allSame = /^(\d)\1{11}$/.test(val);

        if (val.length === 12 && !startsWithZeroOrOne && !allSame) {
            statusIcon.innerHTML = '<i class="fa fa-check-circle" style="color:#009146;"></i>';
            msg.style.display = 'block';
            msg.style.color = '#009146';
            msg.innerHTML = '<i class="fa fa-check"></i> Valid 12-digit Aadhaar Number';
            input.setCustomValidity('');
            return true;
        } else {
            statusIcon.innerHTML = '<i class="fa fa-times-circle" style="color:#dc2626;"></i>';
            msg.style.display = 'block';
            msg.style.color = '#dc2626';
            if (startsWithZeroOrOne) {
                msg.innerText = 'Invalid Aadhaar: Cannot start with 0 or 1';
                input.setCustomValidity('Aadhaar cannot start with 0 or 1');
            } else if (allSame) {
                msg.innerText = 'Invalid Aadhaar: Cannot have all repeating digits';
                input.setCustomValidity('Invalid Aadhaar number');
            } else {
                msg.innerText = 'Please enter a complete 12-digit Aadhaar Number (' + val.length + '/12 digits)';
                input.setCustomValidity('Please enter complete 12 digits');
            }
            return false;
        }
    }

    // Validate Additional Mobile Number (optional, but if provided must be 10 digits and valid)
    var registeredPrimaryPhone = '<?php echo htmlspecialchars($userData['phonenumber'] ?? $_SESSION['user_phone'] ?? ''); ?>';

    function validateAdditionalMobile(input) {
        if (!input) return true;
        var val = input.value.replace(/\D/g, '');
        if (val.length > 10) val = val.substring(0, 10);
        input.value = val;

        var statusIcon = document.getElementById('add_mobile_status_icon');
        var msg = document.getElementById('add_mobile_msg');
        if (!statusIcon || !msg) return true;

        if (val.length === 0) {
            statusIcon.innerHTML = '';
            msg.style.display = 'none';
            input.setCustomValidity('');
            return true;
        }

        if (val.length < 10) {
            statusIcon.innerHTML = '<i class="fa fa-times-circle" style="color:#dc2626;"></i>';
            msg.style.display = 'block';
            msg.style.color = '#dc2626';
            msg.innerText = 'Mobile number must be 10 digits (' + val.length + '/10 digits)';
            input.setCustomValidity('Mobile number must be 10 digits');
            return false;
        }

        var startsWithValid = /^[6-9]/.test(val);
        if (!startsWithValid) {
            statusIcon.innerHTML = '<i class="fa fa-times-circle" style="color:#dc2626;"></i>';
            msg.style.display = 'block';
            msg.style.color = '#dc2626';
            msg.innerText = 'Invalid Mobile: Must start with 6, 7, 8, or 9';
            input.setCustomValidity('Must start with 6, 7, 8, or 9');
            return false;
        }

        if (registeredPrimaryPhone && val === registeredPrimaryPhone) {
            statusIcon.innerHTML = '<i class="fa fa-times-circle" style="color:#dc2626;"></i>';
            msg.style.display = 'block';
            msg.style.color = '#dc2626';
            msg.innerText = 'Additional number cannot be the same as your primary registered mobile (' + registeredPrimaryPhone + ')';
            input.setCustomValidity('Must be different from registered phone');
            return false;
        }

        statusIcon.innerHTML = '<i class="fa fa-check-circle" style="color:#009146;"></i>';
        msg.style.display = 'block';
        msg.style.color = '#009146';
        msg.innerHTML = '<i class="fa fa-check"></i> Valid Additional Mobile Number';
        input.setCustomValidity('');
        return true;
    }

    // Handle "Permanent Address same as Present Address" Checkbox
    function handleSameAddressToggle() {
        var isSame = document.getElementById('same_as_present').checked;
        var permWrap = document.getElementById('permanent_address_wrapper');
        if (isSame) {
            permWrap.style.display = 'none';
            // Sync current values
            document.getElementById('p2_perm_addr').value = document.getElementById('p2_pres_addr').value;
            document.getElementById('p2_perm_vill').value = document.getElementById('p2_pres_vill').value;
            document.getElementById('p2_perm_dist').value = document.getElementById('p2_pres_dist').value;
            document.getElementById('p2_perm_pin').value = document.getElementById('p2_pres_pin').value;
            document.getElementById('p2_perm_state').value = document.getElementById('p2_pres_state').value;
            document.getElementById('p2_perm_country').value = document.getElementById('p2_pres_country').value;
        } else {
            permWrap.style.display = 'block';
        }
    }

    var currentStep = 1;

    function showStatus(msg, isSuccess) {
        var banner = document.getElementById('status_banner');
        if (!banner) return;
        banner.className = isSuccess ? 'alert alert-success' : 'alert alert-danger';
        banner.innerHTML = (isSuccess ? '<i class="fa fa-check-circle"></i> ' : '<i class="fa fa-exclamation-circle"></i> ') + msg;
        banner.style.display = 'block';
        window.scrollTo({ top: banner.offsetTop - 120, behavior: 'smooth' });

        if (isSuccess) {
            setTimeout(function() {
                $(banner).fadeOut();
            }, 5000);
        }
    }

    function goToStep(step) {
        document.getElementById('step_part_1').style.display = (step === 1) ? 'block' : 'none';
        document.getElementById('step_part_2').style.display = (step === 2) ? 'block' : 'none';
        document.getElementById('step_part_3').style.display = (step === 3) ? 'block' : 'none';

        for (var i = 1; i <= 3; i++) {
            var tab = document.getElementById('step_tab_' + i);
            if (i === step) {
                tab.className = 'step-item active';
            } else if (i < step) {
                tab.className = 'step-item completed';
                document.getElementById('badge_icon_' + i).innerHTML = '<i class="fa fa-check"></i>';
            } else {
                tab.className = 'step-item';
                document.getElementById('badge_icon_' + i).innerText = i;
            }
        }
        currentStep = step;
        window.scrollTo({ top: document.querySelector('.step-wizard').offsetTop - 90, behavior: 'smooth' });
    }

    // Save step progressive handler
    function saveProgressiveStep(step, advanceToNext) {
        var form = document.getElementById('form_step_' + step);

        // If step 1, validate Aadhaar and Additional Mobile
        if (step === 1) {
            var aadhaarInput = document.getElementById('p1_aadhaar');
            if (aadhaarInput && !formatAndValidateAadhaar(aadhaarInput)) {
                aadhaarInput.focus();
                showStatus('Please enter a valid 12-digit Indian Aadhaar Number.', false);
                return;
            }
            var addMobileInput = document.getElementById('p1_add_mobile');
            if (addMobileInput && !validateAdditionalMobile(addMobileInput)) {
                addMobileInput.focus();
                showStatus('Please enter a valid 10-digit Additional Mobile Number.', false);
                return;
            }
        }

        // If advancing, check HTML5 validation
        if (advanceToNext) {
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
        }

        var formData = new FormData(form);
        formData.append('step', step);

        var btn = event ? event.currentTarget : null;
        var origText = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
        }

        fetch('api/save-profile-step.php', {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origText;
            }
            if (data.success) {
                showStatus(data.message, true);
                if (data.profile_picture) {
                    var preview = document.getElementById('avatar_preview');
                    if (preview) preview.src = data.profile_picture;
                }
                if (advanceToNext) {
                    goToStep(step + 1);
                }
            } else {
                showStatus(data.message, false);
            }
        })
        .catch(function(err) {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origText;
            }
            showStatus('Network error while saving. Please try again.', false);
        });
    }

    // Final Submission on Step 3
    function submitFinalRegistration() {
        var form = document.getElementById('form_step_3');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        var formData = new FormData(form);
        formData.append('step', 3);
        formData.append('is_final_submit', 1);

        var btn = event ? event.currentTarget : null;
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Finalizing Registration...';
        }

        fetch('api/save-profile-step.php', {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                showStatus('Registration submitted successfully! Redirecting to dashboard...', true);
                setTimeout(function() {
                    window.location.href = data.redirect || 'user-dashboard.php?registered=1';
                }, 1000);
            } else {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-check-circle"></i> <span>Submit Registration</span>';
                }
                showStatus(data.message, false);
            }
        })
        .catch(function(err) {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-check-circle"></i> <span>Submit Registration</span>';
            }
            showStatus('Network error during submission. Please try again.', false);
        });
    }

    // On page load: validate existing Aadhaar if pre-filled
    document.addEventListener('DOMContentLoaded', function() {
        var aadhaarEl = document.getElementById('p1_aadhaar');
        if (aadhaarEl && aadhaarEl.value) {
            formatAndValidateAadhaar(aadhaarEl);
        }
    });
    </script>
</body>

</html>

