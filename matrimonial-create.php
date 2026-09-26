<?php
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: user-login.php?redirect=matrimonial-create.php');
    exit;
}

require_once __DIR__ . '/db.php';
$conn = get_db_connection();

$userId = intval($_SESSION['user_id'] ?? 0);
$userData = null;
if ($conn && $userId > 0) {
    $res = mysqli_query($conn, "SELECT * FROM user_registrtion WHERE id = $userId LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $userData = mysqli_fetch_assoc($res);
    }
}

$errorMsg = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    $profile_for = mysqli_real_escape_string($conn, $_POST['profile_for'] ?? 'self');
    $gender = mysqli_real_escape_string($conn, $_POST['gender'] ?? 'male');
    $full_name = trim(mysqli_real_escape_string($conn, $_POST['full_name'] ?? ''));
    $dob = trim(mysqli_real_escape_string($conn, $_POST['dob'] ?? ''));
    $age = intval($_POST['age'] ?? 0);
    $height = trim(mysqli_real_escape_string($conn, $_POST['height'] ?? ''));
    $marital_status = mysqli_real_escape_string($conn, $_POST['marital_status'] ?? 'unmarried');
    $complexion = trim(mysqli_real_escape_string($conn, $_POST['complexion'] ?? 'Fair'));
    $mother_tongue = trim(mysqli_real_escape_string($conn, $_POST['mother_tongue'] ?? 'Urdu'));
    $cast = trim(mysqli_real_escape_string($conn, $_POST['cast'] ?? 'Eraquee(Iraqi)'));
    $sect = trim(mysqli_real_escape_string($conn, $_POST['sect'] ?? 'Sunni'));
    
    // Education & Career
    $qualification = trim(mysqli_real_escape_string($conn, $_POST['qualification'] ?? ''));
    $occupation = trim(mysqli_real_escape_string($conn, $_POST['occupation'] ?? ''));
    $employed_in = trim(mysqli_real_escape_string($conn, $_POST['employed_in'] ?? 'Private Sector'));
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

    // Auto-calculate age if DOB is provided
    if (!empty($dob)) {
        try {
            $dobObj = new DateTime($dob);
            $age = $dobObj->diff(new DateTime('today'))->y;
        } catch (Exception $e) {}
    }

    if (empty($full_name) || empty($contact_phone)) {
        $errorMsg = 'Please provide the Candidate Full Name and Contact Phone Number.';
    } else {
        // Handle Photo Upload
        $primaryPhotoPath = null;
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
                $newFileName = 'matrimony_' . $userId . '_' . time() . '_' . rand(1000, 9999) . '.' . $fileExt;
                $targetFile = $uploadDir . $newFileName;
                if (move_uploaded_file($fileTmp, $targetFile)) {
                    $primaryPhotoPath = 'uploads/matrimonial/' . $newFileName;
                }
            } else {
                $errorMsg = 'Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP.';
            }
        }

        if (empty($errorMsg)) {
            $photoSqlVal = $primaryPhotoPath ? "'$primaryPhotoPath'" : "NULL";
            $dobSqlVal = !empty($dob) ? "'$dob'" : "NULL";

            $sql = "INSERT INTO matrimonial_profiles (
                created_by_user_id, profile_for, gender, full_name, dob, age, height, marital_status,
                complexion, mother_tongue, cast, sect, qualification, occupation, employed_in, annual_income,
                work_city, work_state, father_name, father_occupation, mother_name, mother_occupation,
                brothers_count, sisters_count, family_type, family_values, native_place, present_city,
                present_state, full_address, contact_person_name, contact_relation, contact_phone,
                contact_whatsapp, partner_preferences, about_candidate, primary_photo, hide_photo_completely,
                status
            ) VALUES (
                $userId, '$profile_for', '$gender', '$full_name', $dobSqlVal, $age, '$height', '$marital_status',
                '$complexion', '$mother_tongue', '$cast', '$sect', '$qualification', '$occupation', '$employed_in', '$annual_income',
                '$work_city', '$work_state', '$father_name', '$father_occupation', '$mother_name', '$mother_occupation',
                $brothers_count, $sisters_count, '$family_type', '$family_values', '$native_place', '$present_city',
                '$present_state', '$full_address', '$contact_person_name', '$contact_relation', '$contact_phone',
                '$contact_whatsapp', '$partner_preferences', '$about_candidate', $photoSqlVal, $hide_photo_completely,
                'active'
            )";

            if (mysqli_query($conn, $sql)) {
                $newProfileId = mysqli_insert_id($conn);
                // Generate official profile code: ERQ-G-xxx (Groom) or ERQ-B-xxx (Bride)
                $prefix = ($gender === 'male') ? 'ERQ-G-' : 'ERQ-B-';
                $profileCode = $prefix . str_pad($newProfileId, 4, '0', STR_PAD_LEFT);
                mysqli_query($conn, "UPDATE matrimonial_profiles SET profile_code = '$profileCode' WHERE id = $newProfileId");

                if ($primaryPhotoPath) {
                    mysqli_query($conn, "INSERT INTO matrimonial_photos (profile_id, photo_path, is_primary) VALUES ($newProfileId, '$primaryPhotoPath', 1)");
                }

                header('Location: matrimonial-manage.php?created=1');
                exit;
            } else {
                $errorMsg = 'Failed to create profile: ' . mysqli_error($conn);
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
    <title>Create Matrimonial Profile | Anjuman Eraquee INDIA</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="images/icon/tabicon.jpeg" type="image/gif">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Droid Sans', sans-serif;
            color: #1e293b;
        }
        .dashboard-header {
            background: #009146;
            color: #fff;
            padding: 16px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-card {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 30px;
            margin: 30px auto;
            border-top: 4px solid #009146;
        }
        .section-title {
            color: #009146;
            font-size: 18px;
            font-weight: 700;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 8px;
            margin-bottom: 20px;
            margin-top: 25px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-group label {
            font-size: 13px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 5px;
        }
        .form-control {
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            box-shadow: none;
            padding: 8px 12px;
            height: 42px;
            font-size: 14px;
        }
        .form-control:focus {
            border-color: #009146;
            box-shadow: 0 0 0 3px rgba(0, 145, 70, 0.15);
        }
        .btn-submit {
            background: #009146;
            color: #fff;
            padding: 12px 36px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 6px;
            border: none;
            transition: all 0.2s;
        }
        .btn-submit:hover {
            background: #006b33;
            color: #fff;
        }
        .btn-back {
            background: #e2e8f0;
            color: #475569;
            padding: 12px 24px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-back:hover {
            background: #cbd5e1;
            color: #1e293b;
        }
        .photo-preview-box {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 3px solid #009146;
            object-fit: cover;
            display: block;
            margin-bottom: 12px;
            background: #f1f5f9;
        }
        .privacy-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 16px;
            margin-top: 15px;
            font-size: 13px;
            color: #166534;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="dashboard-header">
        <div class="container">
            <div class="row" style="display:flex; align-items:center; justify-content:space-between;">
                <div class="col-xs-8">
                    <h2 style="margin:0; font-size:20px; font-weight:700;">💍 Create Matrimonial Biodata</h2>
                    <small style="opacity:0.9;">Kalal Eraquee Community Matrimonial Platform</small>
                </div>
                <div class="col-xs-4 text-right">
                    <a href="matrimonial-manage.php" class="btn btn-default btn-sm" style="font-weight:600; border-radius:20px;">
                        <i class="fa fa-arrow-left"></i> My Profiles
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="form-card">
                    <div style="text-align:center; margin-bottom:25px;">
                        <h3 style="color:#009146; font-weight:700; margin-top:0;">Candidate Registration Form</h3>
                        <p style="color:#64748b; font-size:14px;">Register yourself or your relative to find suitable alliances within our community with complete privacy protection.</p>
                    </div>

                    <?php if (!empty($errorMsg)): ?>
                        <div class="alert alert-danger" style="border-radius:6px;">
                            ⚠️ <?php echo htmlspecialchars($errorMsg); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="matrimonial-create.php" enctype="multipart/form-data" id="matrimonyForm">
                        
                        <!-- 1. Profile Intent & Basic Info -->
                        <div class="section-title">
                            <i class="fa fa-user-circle"></i> 1. Basic Information & Relationship
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Profile Created For *</label>
                                    <select name="profile_for" id="profile_for" class="form-control" required>
                                        <option value="self">Myself (Self)</option>
                                        <option value="son">My Son</option>
                                        <option value="daughter">My Daughter</option>
                                        <option value="brother">My Brother</option>
                                        <option value="sister">My Sister</option>
                                        <option value="relative">Relative / Ward</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Gender *</label>
                                    <select name="gender" id="gender" class="form-control" required>
                                        <option value="male">Dulha (Male / Groom)</option>
                                        <option value="female">Dulhan (Female / Bride)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Candidate Full Name *</label>
                                    <input type="text" name="full_name" class="form-control" placeholder="Candidate's full name" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date of Birth *</label>
                                    <input type="date" name="dob" id="dob" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Age (Years)</label>
                                    <input type="number" name="age" id="age" class="form-control" readonly placeholder="Auto-calculated">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Height *</label>
                                    <select name="height" class="form-control" required>
                                        <option value="4 ft 10 in">4 ft 10 in (147 cm)</option>
                                        <option value="5 ft 0 in">5 ft 0 in (152 cm)</option>
                                        <option value="5 ft 1 in">5 ft 1 in (155 cm)</option>
                                        <option value="5 ft 2 in">5 ft 2 in (157 cm)</option>
                                        <option value="5 ft 3 in">5 ft 3 in (160 cm)</option>
                                        <option value="5 ft 4 in">5 ft 4 in (162 cm)</option>
                                        <option value="5 ft 5 in">5 ft 5 in (165 cm)</option>
                                        <option value="5 ft 6 in" selected>5 ft 6 in (167 cm)</option>
                                        <option value="5 ft 7 in">5 ft 7 in (170 cm)</option>
                                        <option value="5 ft 8 in">5 ft 8 in (172 cm)</option>
                                        <option value="5 ft 9 in">5 ft 9 in (175 cm)</option>
                                        <option value="5 ft 10 in">5 ft 10 in (177 cm)</option>
                                        <option value="5 ft 11 in">5 ft 11 in (180 cm)</option>
                                        <option value="6 ft 0 in">6 ft 0 in (182 cm)</option>
                                        <option value="6 ft 1 in+">6 ft 1 in+ (185+ cm)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Marital Status *</label>
                                    <select name="marital_status" class="form-control" required>
                                        <option value="unmarried">Never Married (Unmarried)</option>
                                        <option value="divorced">Divorced (Talaq Shuda)</option>
                                        <option value="khula_shuda">Khula Shuda</option>
                                        <option value="widowed">Widowed (Bewa)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Complexion</label>
                                    <select name="complexion" class="form-control">
                                        <option value="Very Fair">Very Fair</option>
                                        <option value="Fair" selected>Fair</option>
                                        <option value="Wheatish">Wheatish</option>
                                        <option value="Dark">Dark</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Community Caste *</label>
                                    <select name="cast" class="form-control" required>
                                        <option value="Eraquee(Iraqi)" selected>Eraquee (Iraqi)</option>
                                        <option value="Kalal">Kalal</option>
                                        <option value="Kalwar">Kalwar</option>
                                        <option value="Kalar">Kalar</option>
                                        <option value="Kalal Lari">Kalal Lari</option>
                                        <option value="Kalal Choudhary">Kalal Choudhary</option>
                                        <option value="Araqi">Araqi</option>
                                        <option value="Ranki">Ranki</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Sect</label>
                                    <input type="text" name="sect" class="form-control" value="Sunni">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Mother Tongue</label>
                                    <input type="text" name="mother_tongue" class="form-control" value="Urdu">
                                </div>
                            </div>
                        </div>

                        <!-- 2. Education & Profession -->
                        <div class="section-title">
                            <i class="fa fa-graduation-cap"></i> 2. Education & Professional Career
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Highest Qualification *</label>
                                    <input type="text" name="qualification" class="form-control" placeholder="e.g. B.Tech / MBBS / B.Com / MBA / M.A." required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Occupation / Job Role *</label>
                                    <input type="text" name="occupation" class="form-control" placeholder="e.g. Software Engineer / Doctor / Business / Teacher" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Employed In</label>
                                    <select name="employed_in" class="form-control">
                                        <option value="Private Sector">Private Sector</option>
                                        <option value="Government / PSU">Government / PSU</option>
                                        <option value="Business / Self Employed">Business / Self Employed</option>
                                        <option value="Not Working / Homemaker">Not Working / Homemaker</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Annual Income (Optional)</label>
                                    <select name="annual_income" class="form-control">
                                        <option value="">Not Disclosed</option>
                                        <option value="Under 3 Lakhs">Under ₹3 Lakhs</option>
                                        <option value="3 - 6 Lakhs">₹3 - ₹6 Lakhs</option>
                                        <option value="6 - 10 Lakhs">₹6 - ₹10 Lakhs</option>
                                        <option value="10 - 15 Lakhs">₹10 - ₹15 Lakhs</option>
                                        <option value="15 - 25 Lakhs">₹15 - ₹25 Lakhs</option>
                                        <option value="25 Lakhs+">₹25 Lakhs+</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Working City & State</label>
                                    <input type="text" name="work_city" class="form-control" placeholder="e.g. Delhi / Kolkata / Patna / Bangalore">
                                </div>
                            </div>
                        </div>

                        <!-- 3. Family Background -->
                        <div class="section-title">
                            <i class="fa fa-users"></i> 3. Family Background
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Father's Name</label>
                                    <input type="text" name="father_name" class="form-control" placeholder="Father's full name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Father's Profession</label>
                                    <input type="text" name="father_occupation" class="form-control" placeholder="e.g. Business / Retired Govt Official">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mother's Name</label>
                                    <input type="text" name="mother_name" class="form-control" placeholder="Mother's full name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mother's Profession</label>
                                    <input type="text" name="mother_occupation" class="form-control" placeholder="e.g. Homemaker / Teacher">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Brothers Count</label>
                                    <input type="number" name="brothers_count" class="form-control" value="0" min="0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Sisters Count</label>
                                    <input type="number" name="sisters_count" class="form-control" value="0" min="0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Family Type</label>
                                    <select name="family_type" class="form-control">
                                        <option value="Nuclear">Nuclear Family</option>
                                        <option value="Joint">Joint Family</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Family Values</label>
                                    <select name="family_values" class="form-control">
                                        <option value="Traditional">Traditional</option>
                                        <option value="Moderate">Moderate</option>
                                        <option value="Liberal">Liberal</option>
                                        <option value="Religious">Religious</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Location & Contact Details -->
                        <div class="section-title">
                            <i class="fa fa-map-marker"></i> 4. Location & Contact Person (Protected)
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Native Place (Ancestral)</label>
                                    <input type="text" name="native_place" class="form-control" placeholder="e.g. Siwan / Gaya / Varanasi / Chapra">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Present City *</label>
                                    <input type="text" name="present_city" class="form-control" placeholder="e.g. Patna" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Present State *</label>
                                    <input type="text" name="present_state" class="form-control" placeholder="e.g. Bihar" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Full Residential Address (Strictly Protected - Only shown upon Admin approval)</label>
                                    <textarea name="full_address" class="form-control" rows="2" placeholder="Full house number, street, landmark..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Contact Person Name *</label>
                                    <input type="text" name="contact_person_name" class="form-control" placeholder="e.g. Father's or Guardian's Name" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Relation with Candidate *</label>
                                    <input type="text" name="contact_relation" class="form-control" placeholder="e.g. Father / Mother / Self" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Contact Mobile Number *</label>
                                    <input type="tel" name="contact_phone" class="form-control" placeholder="10-digit mobile" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>WhatsApp Number</label>
                                    <input type="tel" name="contact_whatsapp" class="form-control" placeholder="WhatsApp number">
                                </div>
                            </div>
                        </div>

                        <!-- 5. Partner Preferences & Candidate Bio -->
                        <div class="section-title">
                            <i class="fa fa-heart"></i> 5. Partner Preferences & Remarks
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Partner Preferences / Expectations</label>
                                    <textarea name="partner_preferences" class="form-control" rows="3" placeholder="Desired age range, education, profession, location or values..."></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>About Candidate (Short Bio)</label>
                                    <textarea name="about_candidate" class="form-control" rows="3" placeholder="Brief introduction about personality, hobbies, nature, and lifestyle..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Photograph Upload & Privacy -->
                        <div class="section-title">
                            <i class="fa fa-camera"></i> 6. Candidate Photograph & Privacy Controls
                        </div>
                        <div class="row" style="display:flex; align-items:center; flex-wrap:wrap;">
                            <div class="col-md-3 text-center">
                                <img id="photoPreview" src="images/dummy-avatar.svg" class="photo-preview-box" alt="Preview">
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label>Upload Primary Photo (Passport / Portrait)</label>
                                    <input type="file" name="primary_photo" id="primary_photo" class="form-control" accept="image/*">
                                    <small style="color:#64748b;">Allowed formats: JPG, PNG, WEBP (Max: 5MB). In directory view, photos are strictly protected according to privacy tiers.</small>
                                </div>

                                <div class="checkbox" style="margin-top:15px;">
                                    <label style="font-weight:600; color:#b45309;">
                                        <input type="checkbox" name="hide_photo_completely" value="1"> 
                                        🔒 <strong>High Privacy Option:</strong> Keep candidate photograph completely hidden until direct Admin approval.
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="privacy-box">
                            <i class="fa fa-shield"></i> <strong>Our Community Privacy Promise:</strong><br>
                            Your telephone numbers, WhatsApp, and residential addresses are <strong>never shown publicly</strong>. Only registered community members who have verified candidate profiles and have obtained explicit Admin approval can access your direct contact details.
                        </div>

                        <div style="margin-top:30px; text-align:right; display:flex; justify-content:flex-end; gap:12px;">
                            <a href="matrimonial-manage.php" class="btn-back">Cancel</a>
                            <button type="submit" class="btn-submit">
                                <i class="fa fa-check-circle"></i> Save & Publish Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-calculate age from DOB
        document.getElementById('dob').addEventListener('change', function() {
            var dob = new Date(this.value);
            var today = new Date();
            var age = today.getFullYear() - dob.getFullYear();
            var m = today.getMonth() - dob.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            if (age >= 0 && age < 100) {
                document.getElementById('age').value = age;
            }
        });

        // Instant image preview
        document.getElementById('primary_photo').addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('photoPreview').src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Profile For logic sync
        document.getElementById('profile_for').addEventListener('change', function() {
            var val = this.value;
            var genderSelect = document.getElementById('gender');
            if (val === 'son' || val === 'brother') {
                genderSelect.value = 'male';
            } else if (val === 'daughter' || val === 'sister') {
                genderSelect.value = 'female';
            }
        });
    </script>
</body>
</html>

