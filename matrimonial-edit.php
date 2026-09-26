<?php
session_start();

$isUserLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
$isAdminLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

$profileId = intval($_GET['id'] ?? ($_POST['profile_id'] ?? 0));
if ($profileId <= 0) {
    header('Location: matrimonial.php');
    exit;
}

if (!$isUserLoggedIn && !$isAdminLoggedIn) {
    header('Location: user-login.php?redirect=' . urlencode("matrimonial-edit.php?id=$profileId"));
    exit;
}

require_once __DIR__ . '/db.php';
$conn = get_db_connection();

if (!$conn) {
    die("Database connection error. Please try again later.");
}

// Fetch Profile
$pRes = mysqli_query($conn, "SELECT * FROM matrimonial_profiles WHERE id = $profileId LIMIT 1");
if (!$pRes || mysqli_num_rows($pRes) === 0) {
    header('Location: matrimonial.php');
    exit;
}
$profile = mysqli_fetch_assoc($pRes);

// Check Access Permission: Must be profile creator OR Admin
$currentUserId = intval($_SESSION['user_id'] ?? 0);
$isOwner = ($isUserLoggedIn && $currentUserId === intval($profile['created_by_user_id']));

if (!$isOwner && !$isAdminLoggedIn) {
    header("Location: matrimonial-profile-view.php?id=$profileId&error=unauthorized");
    exit;
}

$errorMsg = '';
$successMsg = '';

// Handle Update Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profile_for = mysqli_real_escape_string($conn, $_POST['profile_for'] ?? $profile['profile_for']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender'] ?? $profile['gender']);
    $full_name = trim(mysqli_real_escape_string($conn, $_POST['full_name'] ?? ''));
    $dob = trim(mysqli_real_escape_string($conn, $_POST['dob'] ?? ''));
    $age = intval($_POST['age'] ?? $profile['age']);
    $height = trim(mysqli_real_escape_string($conn, $_POST['height'] ?? ''));
    $marital_status = mysqli_real_escape_string($conn, $_POST['marital_status'] ?? 'unmarried');
    $complexion = trim(mysqli_real_escape_string($conn, $_POST['complexion'] ?? ''));
    $mother_tongue = trim(mysqli_real_escape_string($conn, $_POST['mother_tongue'] ?? 'Urdu'));
    $cast = trim(mysqli_real_escape_string($conn, $_POST['cast'] ?? 'Eraquee(Iraqi)'));
    $sect = trim(mysqli_real_escape_string($conn, $_POST['sect'] ?? 'Sunni'));

    // Education & Career
    $qualification = trim(mysqli_real_escape_string($conn, $_POST['qualification'] ?? ''));
    $occupation = trim(mysqli_real_escape_string($conn, $_POST['occupation'] ?? ''));
    $employed_in = trim(mysqli_real_escape_string($conn, $_POST['employed_in'] ?? ''));
    $annual_income = trim(mysqli_real_escape_string($conn, $_POST['annual_income'] ?? ''));
    $work_city = trim(mysqli_real_escape_string($conn, $_POST['work_city'] ?? ''));
    $work_state = trim(mysqli_real_escape_string($conn, $_POST['work_state'] ?? ''));

    // Family
    $father_name = trim(mysqli_real_escape_string($conn, $_POST['father_name'] ?? ''));
    $father_occupation = trim(mysqli_real_escape_string($conn, $_POST['father_occupation'] ?? ''));
    $mother_name = trim(mysqli_real_escape_string($conn, $_POST['mother_name'] ?? ''));
    $mother_occupation = trim(mysqli_real_escape_string($conn, $_POST['mother_occupation'] ?? ''));
    $brothers_count = intval($_POST['brothers_count'] ?? 0);
    $sisters_count = intval($_POST['sisters_count'] ?? 0);
    $family_type = mysqli_real_escape_string($conn, $_POST['family_type'] ?? 'Nuclear');
    $family_values = mysqli_real_escape_string($conn, $_POST['family_values'] ?? 'Traditional');

    // Location
    $native_place = trim(mysqli_real_escape_string($conn, $_POST['native_place'] ?? ''));
    $present_city = trim(mysqli_real_escape_string($conn, $_POST['present_city'] ?? ''));
    $present_state = trim(mysqli_real_escape_string($conn, $_POST['present_state'] ?? ''));
    $full_address = trim(mysqli_real_escape_string($conn, $_POST['full_address'] ?? ''));

    // Contact
    $contact_person_name = trim(mysqli_real_escape_string($conn, $_POST['contact_person_name'] ?? ''));
    $contact_relation = trim(mysqli_real_escape_string($conn, $_POST['contact_relation'] ?? ''));
    $contact_phone = trim(mysqli_real_escape_string($conn, $_POST['contact_phone'] ?? ''));
    $contact_whatsapp = trim(mysqli_real_escape_string($conn, $_POST['contact_whatsapp'] ?? ''));

    // Partner Preferences & Bio
    $partner_preferences = trim(mysqli_real_escape_string($conn, $_POST['partner_preferences'] ?? ''));
    $about_candidate = trim(mysqli_real_escape_string($conn, $_POST['about_candidate'] ?? ''));
    $hide_photo_completely = isset($_POST['hide_photo_completely']) ? 1 : 0;

    // Auto-calculate age if DOB changed
    if (!empty($dob)) {
        try {
            $dobObj = new DateTime($dob);
            $age = $dobObj->diff(new DateTime('today'))->y;
        } catch (Exception $e) {}
    }

    if (empty($full_name) || empty($contact_phone)) {
        $errorMsg = 'Candidate Full Name and Contact Phone Number are required.';
    } else {
        // Handle Replacement Photo Upload if provided
        $primaryPhotoPath = $profile['primary_photo'];
        if (isset($_FILES['primary_photo']) && $_FILES['primary_photo']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['primary_photo']['tmp_name'];
            $fileName = $_FILES['primary_photo']['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExt, $allowedExts)) {
                $uploadDir = __DIR__ . '/uploads/matrimonial/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                $newFileName = 'matrimony_' . $profile['created_by_user_id'] . '_' . time() . '_' . rand(1000, 9999) . '.' . $fileExt;
                $targetFile = $uploadDir . $newFileName;
                if (move_uploaded_file($fileTmp, $targetFile)) {
                    $primaryPhotoPath = 'uploads/matrimonial/' . $newFileName;
                    // Record photo in gallery
                    mysqli_query($conn, "INSERT INTO matrimonial_photos (profile_id, photo_path, is_primary) VALUES ($profileId, '$primaryPhotoPath', 1)");
                }
            } else {
                $errorMsg = 'Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP.';
            }
        }

        if (empty($errorMsg)) {
            $dobSqlVal = !empty($dob) ? "'$dob'" : "NULL";
            $photoSqlVal = !empty($primaryPhotoPath) ? "'$primaryPhotoPath'" : "NULL";

            // Determine new status:
            // Regular members editing -> pending_approval for Super Admin/Admin adjudication
            // Admin editing -> can keep active or choose status
            if ($isAdminLoggedIn) {
                $statusVal = mysqli_real_escape_string($conn, $_POST['status'] ?? 'active');
                if (!in_array($statusVal, ['active', 'pending_approval', 'hidden', 'married'])) {
                    $statusVal = 'active';
                }
            } else {
                $statusVal = 'pending_approval';
            }

            $updateSql = "UPDATE matrimonial_profiles SET
                profile_for = '$profile_for',
                gender = '$gender',
                full_name = '$full_name',
                dob = $dobSqlVal,
                age = $age,
                height = '$height',
                marital_status = '$marital_status',
                complexion = '$complexion',
                mother_tongue = '$mother_tongue',
                cast = '$cast',
                sect = '$sect',
                qualification = '$qualification',
                occupation = '$occupation',
                employed_in = '$employed_in',
                annual_income = '$annual_income',
                work_city = '$work_city',
                work_state = '$work_state',
                father_name = '$father_name',
                father_occupation = '$father_occupation',
                mother_name = '$mother_name',
                mother_occupation = '$mother_occupation',
                brothers_count = $brothers_count,
                sisters_count = $sisters_count,
                family_type = '$family_type',
                family_values = '$family_values',
                native_place = '$native_place',
                present_city = '$present_city',
                present_state = '$present_state',
                full_address = '$full_address',
                contact_person_name = '$contact_person_name',
                contact_relation = '$contact_relation',
                contact_phone = '$contact_phone',
                contact_whatsapp = '$contact_whatsapp',
                partner_preferences = '$partner_preferences',
                about_candidate = '$about_candidate',
                primary_photo = $photoSqlVal,
                hide_photo_completely = $hide_photo_completely,
                status = '$statusVal',
                updated_at = NOW()
            WHERE id = $profileId";

            if (mysqli_query($conn, $updateSql)) {
                header("Location: matrimonial-profile-view.php?id=$profileId&updated=1");
                exit;
            } else {
                $errorMsg = 'Failed to update profile: ' . mysqli_error($conn);
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
    <title>Edit Biodata #<?php echo htmlspecialchars($profile['profile_code'] ?? $profile['id']); ?> | Anjuman Eraquee INDIA</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="images/icon/tabicon.jpeg" type="image/gif">
    <style>
        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .form-header-box {
            background: linear-gradient(135deg, #009146 0%, #006b33 100%);
            color: #ffffff;
            padding: 30px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 145, 70, 0.15);
        }
        .card-form {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 35px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }
        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #009146;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        .form-control {
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            height: 42px;
            box-shadow: none;
            font-size: 14px;
        }
        .form-control:focus {
            border-color: #009146;
            box-shadow: 0 0 0 3px rgba(0, 145, 70, 0.15);
        }
        textarea.form-control {
            height: auto;
        }
        .notice-badge {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .current-photo-preview {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #009146;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <div style="background:#ffffff; border-bottom:1px solid #e2e8f0; padding:12px 0;">
        <div class="container" style="display:flex; justify-content:space-between; align-items:center;">
            <a href="matrimonial-profile-view.php?id=<?php echo $profile['id']; ?>" class="btn btn-default btn-sm" style="font-weight:600; border-radius:20px;">
                <i class="fa fa-arrow-left"></i> Cancel & Back to Biodata
            </a>
            <div>
                <?php if ($isAdminLoggedIn): ?>
                    <a href="admin/matrimonial.php" class="btn btn-default btn-sm" style="font-weight:600; border-radius:20px;">
                        🛡️ Admin Console
                    </a>
                <?php else: ?>
                    <a href="matrimonial-manage.php" class="btn btn-default btn-sm" style="font-weight:600; border-radius:20px;">
                        💍 Matrimonial Hub
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container" style="margin-top: 25px; margin-bottom: 60px; max-width: 900px;">
        
        <!-- Header Banner -->
        <div class="form-header-box text-center">
            <span class="badge" style="background:#e5ae49; color:#0f172a; font-weight:800; padding:6px 14px; font-size:13px; margin-bottom:8px;">
                <?php echo htmlspecialchars($profile['profile_code'] ?? 'ERQ-CANDIDATE'); ?>
            </span>
            <h2 style="margin: 6px 0; font-weight: 800; font-size: 24px;">✏️ Edit Matrimonial Biodata</h2>
            <p style="margin: 0; opacity: 0.9; font-size: 14px;">
                Correct or update candidate details. Keep your family biodata accurate and genuine.
            </p>
        </div>

        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger" style="border-radius: 8px; font-weight: 600;">
                <i class="fa fa-exclamation-triangle"></i> <?php echo $errorMsg; ?>
            </div>
        <?php endif; ?>

        <div class="notice-badge">
            <div style="font-weight:700; color:#b45309; font-size:14px;">
                <i class="fa fa-info-circle"></i> Verification Notice
            </div>
            <div style="font-size:13px; color:#92400e; margin-top:4px;">
                <?php if ($isAdminLoggedIn): ?>
                    As an Administrator, you can update this biodata directly. You may choose whether to keep it active or place it under review.
                <?php else: ?>
                    To maintain trust and safety within our community, whenever you edit this biodata, it will be marked as <strong>Under Admin Review</strong> and approved by Admin/Super Admin before updated details appear in the public directory.
                <?php endif; ?>
            </div>
        </div>

        <form method="POST" action="matrimonial-edit.php?id=<?php echo $profile['id']; ?>" enctype="multipart/form-data">
            <input type="hidden" name="profile_id" value="<?php echo $profile['id']; ?>">

            <div class="card-form">
                
                <!-- 1. Profile Core Info -->
                <div class="section-title">
                    <i class="fa fa-user"></i> 1. Profile Core Details
                </div>
                <div class="row">
                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label>Profile Creating For *</label>
                            <select name="profile_for" class="form-control" required>
                                <option value="self" <?php echo $profile['profile_for'] === 'self' ? 'selected' : ''; ?>>Myself</option>
                                <option value="son" <?php echo $profile['profile_for'] === 'son' ? 'selected' : ''; ?>>Son</option>
                                <option value="daughter" <?php echo $profile['profile_for'] === 'daughter' ? 'selected' : ''; ?>>Daughter</option>
                                <option value="brother" <?php echo $profile['profile_for'] === 'brother' ? 'selected' : ''; ?>>Brother</option>
                                <option value="sister" <?php echo $profile['profile_for'] === 'sister' ? 'selected' : ''; ?>>Sister</option>
                                <option value="relative" <?php echo $profile['profile_for'] === 'relative' ? 'selected' : ''; ?>>Relative / Friend</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label>Gender *</label>
                            <select name="gender" class="form-control" required>
                                <option value="male" <?php echo $profile['gender'] === 'male' ? 'selected' : ''; ?>>Male (Dulha)</option>
                                <option value="female" <?php echo $profile['gender'] === 'female' ? 'selected' : ''; ?>>Female (Dulhan)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label>Candidate Full Name *</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($profile['full_name']); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" id="dobInput" class="form-control" value="<?php echo htmlspecialchars($profile['dob'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label>Age (Years) *</label>
                            <input type="number" name="age" id="ageInput" class="form-control" value="<?php echo htmlspecialchars($profile['age'] ?? ''); ?>" min="18" max="75" required>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label>Height *</label>
                            <select name="height" class="form-control" required>
                                <?php
                                $heightOptions = [
                                    "4 ft 10 in", "4 ft 11 in", "5 ft 0 in", "5 ft 1 in", "5 ft 2 in", 
                                    "5 ft 3 in", "5 ft 4 in", "5 ft 5 in", "5 ft 6 in", "5 ft 7 in", 
                                    "5 ft 8 in", "5 ft 9 in", "5 ft 10 in", "5 ft 11 in", "6 ft 0 in", 
                                    "6 ft 1 in", "6 ft 2 in", "6 ft 3 in"
                                ];
                                foreach ($heightOptions as $h) {
                                    $sel = ($profile['height'] === $h) ? 'selected' : '';
                                    echo "<option value=\"$h\" $sel>$h</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label>Marital Status *</label>
                            <select name="marital_status" class="form-control" required>
                                <option value="unmarried" <?php echo $profile['marital_status'] === 'unmarried' ? 'selected' : ''; ?>>Unmarried (Never Married)</option>
                                <option value="divorced" <?php echo $profile['marital_status'] === 'divorced' ? 'selected' : ''; ?>>Divorced (Talaq Shuda)</option>
                                <option value="khula_shuda" <?php echo $profile['marital_status'] === 'khula_shuda' ? 'selected' : ''; ?>>Khula Shuda</option>
                                <option value="widowed" <?php echo $profile['marital_status'] === 'widowed' ? 'selected' : ''; ?>>Widowed (Bewa)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label>Complexion</label>
                            <select name="complexion" class="form-control">
                                <option value="Very Fair" <?php echo $profile['complexion'] === 'Very Fair' ? 'selected' : ''; ?>>Very Fair</option>
                                <option value="Fair" <?php echo $profile['complexion'] === 'Fair' ? 'selected' : ''; ?>>Fair</option>
                                <option value="Wheatish" <?php echo $profile['complexion'] === 'Wheatish' ? 'selected' : ''; ?>>Wheatish</option>
                                <option value="Dusky" <?php echo $profile['complexion'] === 'Dusky' ? 'selected' : ''; ?>>Dusky</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label>Cast / Community *</label>
                            <select name="cast" class="form-control" required>
                                <?php
                                $castList = ['Eraquee(Iraqi)', 'Kalal', 'Kalwar', 'Kalar', 'Kalal Lari', 'Kalal Choudhary', 'Araqi', 'Ranki'];
                                foreach ($castList as $c) {
                                    $sel = ($profile['cast'] === $c) ? 'selected' : '';
                                    echo "<option value=\"$c\" $sel>$c</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 2. Education & Career -->
                <div class="section-title" style="margin-top: 30px;">
                    <i class="fa fa-graduation-cap"></i> 2. Education & Profession
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Highest Qualification *</label>
                            <input type="text" name="qualification" class="form-control" value="<?php echo htmlspecialchars($profile['qualification']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Occupation / Job Title *</label>
                            <input type="text" name="occupation" class="form-control" value="<?php echo htmlspecialchars($profile['occupation']); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label>Employed In</label>
                            <select name="employed_in" class="form-control">
                                <option value="Private Sector" <?php echo $profile['employed_in'] === 'Private Sector' ? 'selected' : ''; ?>>Private Sector</option>
                                <option value="Government / PSU" <?php echo $profile['employed_in'] === 'Government / PSU' ? 'selected' : ''; ?>>Government / PSU</option>
                                <option value="Business / Entrepreneur" <?php echo $profile['employed_in'] === 'Business / Entrepreneur' ? 'selected' : ''; ?>>Business / Self-Employed</option>
                                <option value="Civil Services" <?php echo $profile['employed_in'] === 'Civil Services' ? 'selected' : ''; ?>>Civil Services</option>
                                <option value="Not Working" <?php echo $profile['employed_in'] === 'Not Working' ? 'selected' : ''; ?>>Not Working / Homemaker</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label>Annual Income</label>
                            <input type="text" name="annual_income" class="form-control" value="<?php echo htmlspecialchars($profile['annual_income'] ?? ''); ?>" placeholder="e.g. 8 - 10 Lakhs / Not Disclosed">
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label>Work City & State</label>
                            <div class="row">
                                <div class="col-xs-6" style="padding-right:4px;">
                                    <input type="text" name="work_city" class="form-control" value="<?php echo htmlspecialchars($profile['work_city'] ?? ''); ?>" placeholder="City">
                                </div>
                                <div class="col-xs-6" style="padding-left:4px;">
                                    <input type="text" name="work_state" class="form-control" value="<?php echo htmlspecialchars($profile['work_state'] ?? ''); ?>" placeholder="State">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Family Background -->
                <div class="section-title" style="margin-top: 30px;">
                    <i class="fa fa-home"></i> 3. Family Background
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Father's Name</label>
                            <input type="text" name="father_name" class="form-control" value="<?php echo htmlspecialchars($profile['father_name'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Father's Occupation</label>
                            <input type="text" name="father_occupation" class="form-control" value="<?php echo htmlspecialchars($profile['father_occupation'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Mother's Name</label>
                            <input type="text" name="mother_name" class="form-control" value="<?php echo htmlspecialchars($profile['mother_name'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Mother's Occupation</label>
                            <input type="text" name="mother_occupation" class="form-control" value="<?php echo htmlspecialchars($profile['mother_occupation'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label>Brothers Count</label>
                            <input type="number" name="brothers_count" class="form-control" value="<?php echo htmlspecialchars($profile['brothers_count'] ?? 0); ?>" min="0" max="15">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label>Sisters Count</label>
                            <input type="number" name="sisters_count" class="form-control" value="<?php echo htmlspecialchars($profile['sisters_count'] ?? 0); ?>" min="0" max="15">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label>Family Type</label>
                            <select name="family_type" class="form-control">
                                <option value="Nuclear" <?php echo $profile['family_type'] === 'Nuclear' ? 'selected' : ''; ?>>Nuclear</option>
                                <option value="Joint" <?php echo $profile['family_type'] === 'Joint' ? 'selected' : ''; ?>>Joint</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label>Family Values</label>
                            <select name="family_values" class="form-control">
                                <option value="Traditional" <?php echo $profile['family_values'] === 'Traditional' ? 'selected' : ''; ?>>Traditional</option>
                                <option value="Moderate" <?php echo $profile['family_values'] === 'Moderate' ? 'selected' : ''; ?>>Moderate</option>
                                <option value="Orthodox" <?php echo $profile['family_values'] === 'Orthodox' ? 'selected' : ''; ?>>Orthodox</option>
                                <option value="Liberal" <?php echo $profile['family_values'] === 'Liberal' ? 'selected' : ''; ?>>Liberal</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 4. Location & Contact -->
                <div class="section-title" style="margin-top: 30px;">
                    <i class="fa fa-map-marker"></i> 4. Location & Guardian Contact
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Native Place / Ancestral Town</label>
                            <input type="text" name="native_place" class="form-control" value="<?php echo htmlspecialchars($profile['native_place'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Present City *</label>
                            <input type="text" name="present_city" class="form-control" value="<?php echo htmlspecialchars($profile['present_city'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Present State *</label>
                            <input type="text" name="present_state" class="form-control" value="<?php echo htmlspecialchars($profile['present_state'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Full Residential Address (Tier 3 Protected — Released Only with Admin Approval)</label>
                    <textarea name="full_address" class="form-control" rows="2"><?php echo htmlspecialchars($profile['full_address'] ?? ''); ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label>Contact Person *</label>
                            <input type="text" name="contact_person_name" class="form-control" value="<?php echo htmlspecialchars($profile['contact_person_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label>Relation with Candidate</label>
                            <input type="text" name="contact_relation" class="form-control" value="<?php echo htmlspecialchars($profile['contact_relation'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label>Contact Phone (Tier 3) *</label>
                            <input type="text" name="contact_phone" class="form-control" value="<?php echo htmlspecialchars($profile['contact_phone'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label>WhatsApp Number</label>
                            <input type="text" name="contact_whatsapp" class="form-control" value="<?php echo htmlspecialchars($profile['contact_whatsapp'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- 5. Partner Preferences & Bio -->
                <div class="section-title" style="margin-top: 30px;">
                    <i class="fa fa-heart"></i> 5. Partner Preferences & Candidate Overview
                </div>
                <div class="form-group">
                    <label>Partner Preferences / Expectations</label>
                    <textarea name="partner_preferences" class="form-control" rows="3"><?php echo htmlspecialchars($profile['partner_preferences'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label>About Candidate (Personality, Interests, Hobbies)</label>
                    <textarea name="about_candidate" class="form-control" rows="3"><?php echo htmlspecialchars($profile['about_candidate'] ?? ''); ?></textarea>
                </div>

                <!-- 6. Photograph & Privacy -->
                <div class="section-title" style="margin-top: 30px;">
                    <i class="fa fa-camera"></i> 6. Photograph & Privacy Settings
                </div>
                <div class="row" style="align-items:center;">
                    <div class="col-md-4 text-center">
                        <div style="font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px;">Current Photograph:</div>
                        <img src="<?php echo !empty($profile['primary_photo']) && file_exists(__DIR__ . '/' . $profile['primary_photo']) ? htmlspecialchars($profile['primary_photo']) : 'images/dummy-avatar.svg'; ?>" class="current-photo-preview" id="photoPreview" alt="Current Photo">
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Upload New Photograph (Optional — Replaces Current)</label>
                            <input type="file" name="primary_photo" id="photoInput" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">Accepted formats: JPG, PNG, WEBP. Max size: 5MB.</small>
                        </div>
                        <div class="checkbox" style="margin-top:15px;">
                            <label style="font-weight:600; color:#475569;">
                                <input type="checkbox" name="hide_photo_completely" value="1" <?php echo !empty($profile['hide_photo_completely']) ? 'checked' : ''; ?>>
                                <strong>Strict Privacy Mode:</strong> Hide photograph completely from directory until direct contact is approved.
                            </label>
                        </div>
                    </div>
                </div>

                <?php if ($isAdminLoggedIn): ?>
                    <!-- Admin Status Control -->
                    <div class="section-title" style="margin-top: 30px; color:#b45309; border-bottom-color:#fef3c7;">
                        <i class="fa fa-shield"></i> 7. Administrative Status Adjudication
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Profile Status</label>
                                <select name="status" class="form-control" style="border-color:#f59e0b; font-weight:700;">
                                    <option value="active" <?php echo $profile['status'] === 'active' ? 'selected' : ''; ?>>✅ Active (Live in Directory)</option>
                                    <option value="pending_approval" <?php echo $profile['status'] === 'pending_approval' ? 'selected' : ''; ?>>⏳ Pending Approval (Under Review)</option>
                                    <option value="hidden" <?php echo $profile['status'] === 'hidden' ? 'selected' : ''; ?>>🔒 Hidden / Suspended</option>
                                    <option value="married" <?php echo $profile['status'] === 'married' ? 'selected' : ''; ?>>💍 Married / Settled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="margin-top: 35px; border-top: 2px solid #e2e8f0; padding-top: 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                    <a href="matrimonial-profile-view.php?id=<?php echo $profile['id']; ?>" class="btn btn-default" style="font-weight:600; border-radius:6px; padding:10px 20px;">
                        Discard Changes
                    </a>
                    <button type="submit" class="btn btn-success" style="background:#009146; border:none; padding:12px 30px; font-weight:700; border-radius:6px; font-size:15px; box-shadow:0 4px 12px rgba(0,145,70,0.25);">
                        <i class="fa fa-check-circle"></i> Save & Submit for Verification
                    </button>
                </div>

            </div>
        </form>

    </div>

    <script>
        // Real-time Age Calculation from DOB
        document.getElementById('dobInput').addEventListener('change', function() {
            var dobVal = this.value;
            if (dobVal) {
                var dob = new Date(dobVal);
                var today = new Date();
                var age = today.getFullYear() - dob.getFullYear();
                var m = today.getMonth() - dob.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                    age--;
                }
                if (age >= 18) {
                    document.getElementById('ageInput').value = age;
                }
            }
        });

        // Instant Image Preview on file select
        document.getElementById('photoInput').addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(evt) {
                    document.getElementById('photoPreview').src = evt.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>

</body>
</html>

