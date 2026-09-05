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
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .dashboard-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
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
        .btn-logout {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 4px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-logout:hover {
            background: #c82333;
            color: #fff;
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
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fa fa-user-circle"></i> Member Dashboard</h2>
                <small>Anjuman Eraquee INDIA</small>
            </div>
            <div>
                <a href="index.html" class="btn btn-sm btn-light font-weight-bold mr-2"><i class="fa fa-home"></i> Home</a>
                <a href="user-logout.php" class="btn-logout"><i class="fa fa-sign-out"></i> Logout</a>
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

    <?php if (!$userData): ?>
        <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle"></i> Member details could not be retrieved. Please try logging in again.
        </div>
    <?php else: ?>

        <!-- Welcome Banner -->
        <div class="alert alert-success d-flex justify-content-between align-items-center mb-4 flex-wrap" style="gap: 15px;">
            <div>
                <h4 class="mb-1">Welcome, <strong><?php echo htmlspecialchars($userData['username'] ?? 'Member'); ?></strong> <span class="badge-verified"><i class="fa fa-check"></i> Verified Member</span></h4>
                <p class="mb-0 text-muted" style="font-size: 13px;">Registered Phone: +91 <?php echo htmlspecialchars($userData['phonenumber'] ?? ''); ?> | Member ID: #<?php echo str_pad($userData['id'] ?? 1, 5, '0', STR_PAD_LEFT); ?></p>
            </div>
            <div>
                <a href="download-certificate.php" class="btn btn-light font-weight-bold" style="color: #009146; box-shadow: 0 2px 6px rgba(0,0,0,0.1); padding: 8px 16px;">
                    <i class="fa fa-file-pdf-o text-danger"></i> Download Membership Certificate (PDF)
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Personal Information -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h4><i class="fa fa-id-card-o"></i> Personal Information</h4>
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
                            <div class="info-label">Date of Birth / Age</div>
                            <div class="info-value"><?php echo !empty($userData['dob']) ? htmlspecialchars($userData['dob']) : 'N/A'; ?> (<?php echo htmlspecialchars($userData['age'] ?? 'N/A'); ?> yrs)</div>
                        </div>
                        <div class="col-6">
                            <div class="info-label">Marital Status</div>
                            <div class="info-value"><?php echo htmlspecialchars($userData['maritalstatus'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="col-12">
                            <div class="info-label">Native Place</div>
                            <div class="info-value"><?php echo htmlspecialchars($userData['nativeplace'] ?? 'N/A'); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h4><i class="fa fa-phone"></i> Contact Details</h4>
                    <div class="row">
                        <div class="col-6">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value"><?php echo htmlspecialchars($userData['phonenumber'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="col-6">
                            <div class="info-label">WhatsApp Number</div>
                            <div class="info-value"><?php echo htmlspecialchars($userData['whatsappnumber'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="col-12">
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
                    <h4><i class="fa fa-briefcase"></i> Qualification & Occupation</h4>
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

