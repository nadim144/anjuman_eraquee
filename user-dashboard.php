<?php
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: user-login.php');
    exit;
}

require_once __DIR__ . '/db.php';
$conn = get_db_connection();

$userData = null;
if ($conn && isset($_SESSION['user_id'])) {
    $userId = intval($_SESSION['user_id']);
    $res = mysqli_query($conn, "SELECT * FROM user_registrtion WHERE id = $userId");
    if ($res && mysqli_num_rows($res) > 0) {
        $userData = mysqli_fetch_assoc($res);
    }
} else if ($conn && isset($_SESSION['user_phone'])) {
    $phone = mysqli_real_escape_string($conn, $_SESSION['user_phone']);
    $res = mysqli_query($conn, "SELECT * FROM user_registrtion WHERE phonenumber = '$phone' LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $userData = mysqli_fetch_assoc($res);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Profile Dashboard | Anjuman Eraquee INDIA</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Droid Sans', sans-serif;
        }
        .dashboard-header {
            background: #009146;
            color: #fff;
            padding: 16px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .dashboard-header .header-brand {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .dashboard-header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            line-height: 1.25;
            letter-spacing: -0.2px;
        }
        .dashboard-header small {
            font-size: 12px;
            opacity: 0.9;
            letter-spacing: 0.3px;
            margin-top: 2px;
        }
        .dashboard-header .header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .btn-dash-nav {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: #ffffff;
            color: #1e293b;
            font-weight: 600;
            font-size: 13px;
            line-height: 1;
            padding: 7px 14px;
            border-radius: 6px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .btn-dash-nav:hover, .btn-dash-nav:focus {
            background: #f1f5f9;
            color: #0f172a;
            text-decoration: none;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
        }
        .btn-dash-nav.btn-logout {
            color: #dc2626;
            background: #ffffff;
            border: 1px solid #fee2e2;
        }
        .btn-dash-nav.btn-logout:hover {
            background: #fef2f2;
            color: #b91c1c;
            border-color: #fca5a5;
        }
        .btn-dash-nav i {
            font-size: 13px;
        }
        @media (max-width: 767px) {
            .dashboard-header {
                padding: 12px 0;
            }
            .dashboard-header h2 {
                font-size: 17px;
                white-space: nowrap;
            }
            .dashboard-header small {
                font-size: 11px;
            }
            .dashboard-header .header-actions {
                gap: 6px;
            }
            .btn-dash-nav {
                font-size: 12px;
                padding: 6px 10px;
                gap: 4px;
                border-radius: 5px;
            }
            .btn-dash-nav i {
                font-size: 12px;
            }
        }
        @media (max-width: 420px) {
            .dashboard-header h2 {
                font-size: 15px;
            }
            .dashboard-header small {
                font-size: 10px;
            }
            .btn-dash-nav {
                font-size: 11px;
                padding: 5px 8px;
            }
        }
        .dashboard-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 25px;
            margin-bottom: 25px;
            border-left: 4px solid #009146;
        }
        .dashboard-card h4 {
            color: #009146;
            font-weight: 700;
            font-size: 18px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .info-label {
            font-weight: 600;
            color: #555;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 15px;
            color: #111;
            font-weight: 500;
            margin-bottom: 15px;
            word-break: break-word;
        }
        .badge-verified {
            background: #28a745;
            color: #fff;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 12px;
            margin-left: 10px;
        }
    </style>
</head>
<body>

<div class="dashboard-header mb-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-nowrap" style="gap: 10px;">
            <div class="header-brand">
                <h2><i class="fa fa-user-circle"></i> Member Dashboard</h2>
                <small>Anjuman Eraquee INDIA</small>
            </div>
            <div class="header-actions">
                <a href="index.html" class="btn-dash-nav"><i class="fa fa-home" style="color:#009146;"></i> Home</a>
                <a href="matrimonial-manage.php" class="btn-dash-nav"><i class="fa fa-heart" style="color:#db2777;"></i> Matrimonial</a>
                <a href="user-logout.php" class="btn-dash-nav btn-logout"><i class="fa fa-sign-out"></i> Logout</a>
            </div>
        </div>
    </div>
</div>

<div class="container">

    <?php if (isset($_SESSION['password_reset_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show font-weight-bold mb-4">
            <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['password_reset_success']); unset($_SESSION['password_reset_success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['registered'])): ?>
        <div class="alert alert-success alert-dismissible fade show font-weight-bold mb-4" style="background:#e8f5e9; border:1.5px solid #2e7d32; color:#1b5e20;">
            <i class="fa fa-check-circle" style="font-size:18px;"></i> 
            Congratulations! Your Membership details have been saved successfully. You can download your official Membership Certificate below or edit your details anytime.
        </div>
    <?php endif; ?>

    <?php if (!$userData): ?>
        <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle"></i> Member details could not be retrieved. Please try logging in again.
        </div>
    <?php else: ?>

        <?php
        $myMatrimonyCount = 0;
        $pendingInterestsCount = 0;
        if ($conn && isset($userData['id'])) {
            $uid = intval($userData['id']);
            $mCntRes = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM matrimonial_profiles WHERE created_by_user_id = $uid");
            if ($mCntRes && $mc = mysqli_fetch_assoc($mCntRes)) {
                $myMatrimonyCount = intval($mc['cnt']);
            }
            $intRes = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM matrimonial_interests WHERE receiver_user_id = $uid AND status = 'pending'");
            if ($intRes && $ic = mysqli_fetch_assoc($intRes)) {
                $pendingInterestsCount = intval($ic['cnt']);
            }
        }
        ?>

        <?php if ($pendingInterestsCount > 0): ?>
            <div class="alert alert-info mb-4" style="background:#fdf2f8; border:1.5px solid #f472b6; color:#9d174d; border-radius:8px; font-weight:600; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div>
                    <i class="fa fa-heart" style="font-size:18px; color:#db2777;"></i>
                    You have <strong><?php echo $pendingInterestsCount; ?> new proposal interest(s)</strong> received from Eraquee families!
                </div>
                <a href="matrimonial-manage.php" class="btn btn-sm btn-danger" style="background:#db2777; border:none; font-weight:700; border-radius:20px; padding:6px 16px;">
                    Review Interests
                </a>
            </div>
        <?php endif; ?>

        <!-- Welcome Banner -->
        <div class="alert alert-success d-flex justify-content-between align-items-center mb-4 flex-wrap" style="gap: 15px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <?php 
                $dashPic = (!empty($userData['profile_picture']) && file_exists(__DIR__ . '/' . $userData['profile_picture'])) 
                    ? htmlspecialchars($userData['profile_picture']) 
                    : 'images/dummy-avatar.svg';
                ?>
                <img src="<?php echo $dashPic; ?>" alt="Profile Photo" onerror="this.src='images/dummy-avatar.svg'" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.15); background:#ffffff;">
                <div>
                    <h4 class="mb-1">Welcome, <strong><?php echo htmlspecialchars($userData['username'] ?? 'Member'); ?></strong> <span class="badge-verified"><i class="fa fa-check"></i> Verified Member</span></h4>
                    <p class="mb-0 text-muted" style="font-size: 13px;">Registered Phone: +91 <?php echo htmlspecialchars($userData['phonenumber'] ?? ''); ?> | Member ID: #<?php echo str_pad($userData['id'] ?? 1, 5, '0', STR_PAD_LEFT); ?></p>
                </div>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="registration.php" class="btn btn-outline-success font-weight-bold" style="background: #ffffff; color: #009146; border: 1.5px solid #009146; box-shadow: 0 2px 6px rgba(0,0,0,0.06); padding: 8px 16px;">
                    <i class="fa fa-pencil-square-o"></i> Edit Profile Details
                </a>
                <a href="download-certificate.php" class="btn btn-light font-weight-bold" style="color: #009146; box-shadow: 0 2px 6px rgba(0,0,0,0.1); padding: 8px 16px;">
                    <i class="fa fa-file-pdf-o text-danger"></i> Download Membership Certificate (PDF)
                </a>
            </div>
        </div>

        <!-- Matrimonial Center Spotlight Banner -->
        <div style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1.5px solid #86efac; border-radius: 10px; padding: 20px 24px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; box-shadow: 0 4px 12px rgba(0, 145, 70, 0.05);">
            <div>
                <h4 style="margin: 0 0 5px; color: #009146; font-weight: 800; font-size: 18px;">
                    💍 Community Matrimonial Center
                </h4>
                <p style="margin: 0; color: #475569; font-size: 13px;">
                    Create and manage matrimonial biodatas for yourself, your son, daughter, brother, sister, or relative with multi-tiered privacy protection.
                </p>
            </div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="matrimonial.php" class="btn btn-default" style="font-weight: 700; border-radius: 6px; font-size: 13px; padding: 8px 14px;">
                    🔍 Browse Alliances
                </a>
                <a href="matrimonial-manage.php" class="btn btn-success" style="background: #009146; border: none; font-weight: 700; border-radius: 6px; font-size: 13px; padding: 8px 16px;">
                    My Matrimonial Hub (<?php echo $myMatrimonyCount; ?>)
                </a>
                <a href="matrimonial-create.php" class="btn btn-warning" style="background: #e5ae49; color: #0f172a; border: none; font-weight: 700; border-radius: 6px; font-size: 13px; padding: 8px 16px;">
                    <i class="fa fa-plus-circle"></i> Create Profile
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Personal Information -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-3" style="border-bottom: 2px solid #eee; padding-bottom: 10px;">
                        <h4 style="margin: 0; border: none; padding: 0;"><i class="fa fa-id-card-o"></i> Personal Information</h4>
                        <a href="registration.php" class="btn btn-sm btn-outline-success" style="font-size: 12px; font-weight: 600;"><i class="fa fa-pencil"></i> Edit</a>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="info-label">Full Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($userData['username'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="col-6">
                            <div class="info-label">Father's Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($userData['fathername'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="col-6">
                            <div class="info-label">Mother's Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($userData['mothername'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="col-6">
                            <div class="info-label">Grandfather's Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($userData['grandfathername'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="col-6">
                            <div class="info-label">Date of Birth</div>
                            <div class="info-value"><?php echo !empty($userData['dob']) ? htmlspecialchars($userData['dob']) : 'N/A'; ?></div>
                        </div>
                        <div class="col-6">
                            <div class="info-label">Gender</div>
                            <div class="info-value"><?php echo ucfirst(htmlspecialchars($userData['gender'] ?? 'N/A')); ?></div>
                        </div>
                        <div class="col-6">
                            <div class="info-label">Marital Status</div>
                            <div class="info-value"><?php echo ucfirst(htmlspecialchars($userData['maritalstatus'] ?? 'N/A')); ?></div>
                        </div>
                        <div class="col-6">
                            <div class="info-label">Cast</div>
                            <div class="info-value"><?php echo htmlspecialchars($userData['cast'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="col-6">
                            <div class="info-label">Aadhaar Number</div>
                            <div class="info-value">
                                <?php 
                                if (!empty($userData['aadhaar_number'])) {
                                    $digits = preg_replace('/\D/', '', $userData['aadhaar_number']);
                                    echo htmlspecialchars(trim(chunk_split($digits, 4, ' ')));
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </div>
                        </div>
                        <?php if (!empty($userData['nativeplace'])): ?>
                        <div class="col-6">
                            <div class="info-label">Native Place</div>
                            <div class="info-value"><?php echo htmlspecialchars($userData['nativeplace']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-3" style="border-bottom: 2px solid #eee; padding-bottom: 10px;">
                        <h4 style="margin: 0; border: none; padding: 0;"><i class="fa fa-phone"></i> Contact Details</h4>
                        <a href="registration.php" class="btn btn-sm btn-outline-success" style="font-size: 12px; font-weight: 600;"><i class="fa fa-pencil"></i> Edit</a>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="info-label">Primary Mobile</div>
                            <div class="info-value"><?php echo htmlspecialchars($userData['phonenumber'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="col-6">
                            <div class="info-label">Additional Mobile</div>
                            <div class="info-value"><?php echo !empty($userData['additional_mobile']) ? htmlspecialchars($userData['additional_mobile']) : 'N/A'; ?></div>
                        </div>
                        <div class="col-6">
                            <div class="info-label">WhatsApp Number</div>
                            <div class="info-value"><?php echo !empty($userData['whatsappnumber']) ? htmlspecialchars($userData['whatsappnumber']) : 'N/A'; ?></div>
                        </div>
                        <div class="col-6">
                            <div class="info-label">Email Address</div>
                            <div class="info-value"><?php echo htmlspecialchars($userData['email'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="col-12">
                            <div class="info-label">Present Address</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($userData['presentaddress'] ?? ''); ?>, 
                                <?php echo htmlspecialchars($userData['presentvillatpost'] ?? ''); ?>, 
                                <?php echo htmlspecialchars($userData['presentdistrict'] ?? ''); ?>, 
                                <?php echo htmlspecialchars($userData['presentstate'] ?? ''); ?> - <?php echo htmlspecialchars($userData['presentpincode'] ?? ''); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Education & Occupation -->
            <div class="col-md-12">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-3" style="border-bottom: 2px solid #eee; padding-bottom: 10px;">
                        <h4 style="margin: 0; border: none; padding: 0;"><i class="fa fa-briefcase"></i> Qualification & Occupation</h4>
                        <a href="registration.php" class="btn btn-sm btn-outline-success" style="font-size: 12px; font-weight: 600;"><i class="fa fa-pencil"></i> Edit</a>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-label">Qualification</div>
                            <div class="info-value"><?php echo htmlspecialchars($userData['qulification'] ?? 'N/A'); ?></div>
                            <?php if (!empty($userData['qualificationdetails'])): ?>
                                <div class="info-label">Qualification Details</div>
                                <div class="info-value"><?php echo htmlspecialchars($userData['qualificationdetails']); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Occupation</div>
                            <div class="info-value"><?php echo htmlspecialchars($userData['occupation'] ?? 'N/A'); ?></div>
                            <?php if (!empty($userData['occupationdetails'])): ?>
                                <div class="info-label">Occupation Details</div>
                                <div class="info-value"><?php echo htmlspecialchars($userData['occupationdetails']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>

</body>
</html>

