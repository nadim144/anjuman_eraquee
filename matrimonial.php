<?php
session_start();
require_once __DIR__ . '/db.php';
$conn = get_db_connection();

$isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
$currentUserId = intval($_SESSION['user_id'] ?? 0);

// Fetch logged in user's matrimonial profiles (to determine Tier 2 reciprocal access)
$myMaleProfiles = [];
$myFemaleProfiles = [];
if ($conn && $isLoggedIn && $currentUserId > 0) {
    $pRes = mysqli_query($conn, "SELECT id, profile_code, full_name, gender, status FROM matrimonial_profiles WHERE created_by_user_id = $currentUserId AND status = 'active'");
    if ($pRes) {
        while ($p = mysqli_fetch_assoc($pRes)) {
            if ($p['gender'] === 'male') {
                $myMaleProfiles[] = $p;
            } else {
                $myFemaleProfiles[] = $p;
            }
        }
    }
}

// Handle Interest Submission directly from directory
$feedbackMsg = '';
$feedbackType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_interest' && $conn) {
    if (!$isLoggedIn) {
        header('Location: user-login.php?redirect=matrimonial.php');
        exit;
    }

    $targetProfileId = intval($_POST['target_profile_id'] ?? 0);
    $senderProfileId = intval($_POST['sender_profile_id'] ?? 0);
    $interestMessage = trim(mysqli_real_escape_string($conn, $_POST['interest_message'] ?? 'We liked your profile and would like to explore this alliance.'));

    if ($targetProfileId > 0 && $senderProfileId > 0) {
        // Fetch target profile info
        $tRes = mysqli_query($conn, "SELECT id, created_by_user_id, profile_code FROM matrimonial_profiles WHERE id = $targetProfileId");
        if ($tRes && $targetProf = mysqli_fetch_assoc($tRes)) {
            $targetUserId = intval($targetProf['created_by_user_id']);

            if ($targetUserId === $currentUserId) {
                $feedbackMsg = "You cannot show interest in your own profile!";
                $feedbackType = 'warning';
            } else {
                // Check if already sent
                $chk = mysqli_query($conn, "SELECT id, status FROM matrimonial_interests WHERE sender_profile_id = $senderProfileId AND receiver_profile_id = $targetProfileId");
                if ($chk && mysqli_num_rows($chk) > 0) {
                    $feedbackMsg = "You have already sent an interest to profile #" . htmlspecialchars($targetProf['profile_code']) . ".";
                    $feedbackType = 'warning';
                } else {
                    $insSql = "INSERT INTO matrimonial_interests (sender_user_id, sender_profile_id, receiver_user_id, receiver_profile_id, message, status) 
                               VALUES ($currentUserId, $senderProfileId, $targetUserId, $targetProfileId, '$interestMessage', 'pending')";
                    if (mysqli_query($conn, $insSql)) {
                        $feedbackMsg = "❤️ Your interest has been sent to the family of " . htmlspecialchars($targetProf['profile_code']) . "! They will be notified in their dashboard.";
                        $feedbackType = 'success';
                    } else {
                        $feedbackMsg = "Failed to send interest: " . mysqli_error($conn);
                        $feedbackType = 'danger';
                    }
                }
            }
        }
    } else {
        $feedbackMsg = "Please select which of your candidate profiles is expressing interest.";
        $feedbackType = 'danger';
    }
}

// Search & Filter Parameters
$filterGender = $_GET['gender'] ?? '';
$filterMarital = $_GET['marital'] ?? '';
$filterCast = $_GET['cast'] ?? '';
$filterCity = trim($_GET['city'] ?? '');
$filterMinAge = intval($_GET['min_age'] ?? 18);
$filterMaxAge = intval($_GET['max_age'] ?? 60);

// Build SQL Query
$where = ["status = 'active'"];

if ($filterGender === 'male' || $filterGender === 'dulha') {
    $where[] = "gender = 'male'";
} else if ($filterGender === 'female' || $filterGender === 'dulhan') {
    $where[] = "gender = 'female'";
}

if (!empty($filterMarital)) {
    $escM = mysqli_real_escape_string($conn, $filterMarital);
    $where[] = "marital_status = '$escM'";
}

if (!empty($filterCast)) {
    $escC = mysqli_real_escape_string($conn, $filterCast);
    $where[] = "cast = '$escC'";
}

if (!empty($filterCity)) {
    $escCity = mysqli_real_escape_string($conn, $filterCity);
    $where[] = "(present_city LIKE '%$escCity%' OR native_place LIKE '%$escCity%' OR present_state LIKE '%$escCity%')";
}

if ($filterMinAge > 18) {
    $where[] = "age >= $filterMinAge";
}
if ($filterMaxAge < 60 && $filterMaxAge > 18) {
    $where[] = "age <= $filterMaxAge";
}

$whereSql = "WHERE " . implode(" AND ", $where);
$profiles = [];
$totalProfiles = 0;

if ($conn) {
    $cntRes = mysqli_query($conn, "SELECT COUNT(*) as total FROM matrimonial_profiles $whereSql");
    if ($cntRes && $cntRow = mysqli_fetch_assoc($cntRes)) {
        $totalProfiles = intval($cntRow['total']);
    }

    $q = "SELECT * FROM matrimonial_profiles $whereSql ORDER BY id DESC LIMIT 60";
    $res = mysqli_query($conn, $q);
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $profiles[] = $r;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Community Matrimonial | Anjuman Eraquee INDIA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="images/icon/tabicon.jpeg" type="image/gif">
    <style>
        body {
            background-color: #f8fafc;
            color: #1e293b;
        }
        .matrimony-banner {
            background: linear-gradient(135deg, #009146 0%, #006b33 100%);
            color: #ffffff;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        .filter-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
            margin-bottom: 25px;
        }
        .candidate-card {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            padding: 20px;
            margin-bottom: 25px;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: calc(100% - 25px);
        }
        .candidate-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 145, 70, 0.12);
            border-color: #86efac;
        }
        .card-photo-wrapper {
            position: relative;
            width: 110px;
            height: 110px;
            margin: 0 auto 15px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #009146;
            background: #f1f5f9;
        }
        .candidate-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-locked-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(5px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            text-align: center;
            padding: 6px;
        }
        .badge-marital {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
        }
        .marital-unmarried { background: #dcfce7; color: #15803d; }
        .marital-divorced { background: #fef3c7; color: #b45309; }
        .marital-khula { background: #fee2e2; color: #b91c1c; }
        .marital-widowed { background: #e0f2fe; color: #0369a1; }
        .btn-card-action {
            padding: 7px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }
        .btn-interest {
            background: #fdf2f8;
            color: #db2777;
            border: 1px solid #fbcfe8;
        }
        .btn-interest:hover {
            background: #db2777;
            color: #fff;
        }
        .btn-view-profile {
            background: #009146;
            color: #fff;
            border: 1px solid #009146;
        }
        .btn-view-profile:hover {
            background: #006b33;
            color: #fff;
        }
        .privacy-pill-notice {
            background: #f1f5f9;
            border-radius: 20px;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
    </style>
</head>
<body>

    <!-- Main Navigation Header -->
    <header>
        <div class="topbar hidden-sm-down">
            <div class="container">
                <div class="row" style="display:flex; justify-content:space-between; align-items:center;">
                    <div class="col-sm-8">
                        <span style="color:#64748b; font-size:13px;">
                            Anjuman Eraquee INDIA — Centralized Community Matrimonial Directory
                        </span>
                    </div>
                    <div class="col-sm-4 text-right">
                        <?php if ($isLoggedIn): ?>
                            <a href="matrimonial-manage.php" style="color:#009146; font-weight:700; margin-right:12px;">💍 My Matrimonial Hub</a>
                            <a href="user-dashboard.php" style="color:#1e293b; font-weight:600; margin-right:12px;">Dashboard</a>
                            <a href="user-logout.php" style="color:#dc2626; font-weight:600;">Logout</a>
                        <?php else: ?>
                            <a href="user-login.php" style="color:#009146; font-weight:700; margin-right:10px;">Member Login</a>
                            <a href="registration.html" style="color:#1e293b; font-weight:600;">Join Membership</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Banner -->
    <section class="matrimony-banner">
        <div class="container">
            <div class="row" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap;">
                <div class="col-md-8">
                    <h1 style="margin:0 0 8px; font-weight:800; font-size:28px;">💍 Kalal Eraquee Community Matrimonial</h1>
                    <p style="margin:0; font-size:15px; opacity:0.95;">
                        A dignified, secure matchmaking service exclusively for the Kalal Eraquee (Iraqi) Biradri across India.
                    </p>
                </div>
                <div class="col-md-4 text-right" style="margin-top:10px;">
                    <?php if ($isLoggedIn): ?>
                        <a href="matrimonial-create.php" class="btn btn-warning" style="background:#e5ae49; color:#0f172a; font-weight:700; border:none; padding:10px 22px; border-radius:25px;">
                            <i class="fa fa-plus-circle"></i> Create Matrimony Profile
                        </a>
                    <?php else: ?>
                        <a href="user-login.php" class="btn btn-warning" style="background:#e5ae49; color:#0f172a; font-weight:700; border:none; padding:10px 22px; border-radius:25px;">
                            <i class="fa fa-lock"></i> Login to Register Profile
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        
        <?php if (!empty($feedbackMsg)): ?>
            <div class="alert alert-<?php echo $feedbackType; ?>" style="border-radius:6px; font-weight:600;">
                <?php echo $feedbackMsg; ?>
            </div>
        <?php endif; ?>

        <!-- Search & Filter Bar -->
        <div class="filter-card">
            <form method="GET" action="matrimonial.php" class="row" style="display:flex; align-items:flex-end; flex-wrap:wrap;">
                <div class="col-md-3 col-sm-6" style="margin-bottom:10px;">
                    <label style="font-size:12px; font-weight:700; color:#475569;">Looking For</label>
                    <select name="gender" class="form-control">
                        <option value="">All Alliances (Dulha & Dulhan)</option>
                        <option value="male" <?php echo $filterGender === 'male' ? 'selected' : ''; ?>>Dulha (Grooms)</option>
                        <option value="female" <?php echo $filterGender === 'female' ? 'selected' : ''; ?>>Dulhan (Brides)</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6" style="margin-bottom:10px;">
                    <label style="font-size:12px; font-weight:700; color:#475569;">Marital Status</label>
                    <select name="marital" class="form-control">
                        <option value="">All Marital Statuses</option>
                        <option value="unmarried" <?php echo $filterMarital === 'unmarried' ? 'selected' : ''; ?>>Never Married (Unmarried)</option>
                        <option value="divorced" <?php echo $filterMarital === 'divorced' ? 'selected' : ''; ?>>Divorced (Talaq Shuda)</option>
                        <option value="khula_shuda" <?php echo $filterMarital === 'khula_shuda' ? 'selected' : ''; ?>>Khula Shuda</option>
                        <option value="widowed" <?php echo $filterMarital === 'widowed' ? 'selected' : ''; ?>>Widowed (Bewa)</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-4" style="margin-bottom:10px;">
                    <label style="font-size:12px; font-weight:700; color:#475569;">Community Caste</label>
                    <select name="cast" class="form-control">
                        <option value="">All Subdivisions</option>
                        <option value="Eraquee(Iraqi)" <?php echo $filterCast === 'Eraquee(Iraqi)' ? 'selected' : ''; ?>>Eraquee(Iraqi)</option>
                        <option value="Kalal" <?php echo $filterCast === 'Kalal' ? 'selected' : ''; ?>>Kalal</option>
                        <option value="Kalwar" <?php echo $filterCast === 'Kalwar' ? 'selected' : ''; ?>>Kalwar</option>
                        <option value="Kalar" <?php echo $filterCast === 'Kalar' ? 'selected' : ''; ?>>Kalar</option>
                        <option value="Kalal Lari" <?php echo $filterCast === 'Kalal Lari' ? 'selected' : ''; ?>>Kalal Lari</option>
                        <option value="Araqi" <?php echo $filterCast === 'Araqi' ? 'selected' : ''; ?>>Araqi</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-4" style="margin-bottom:10px;">
                    <label style="font-size:12px; font-weight:700; color:#475569;">City / State</label>
                    <input type="text" name="city" class="form-control" placeholder="e.g. Patna, Delhi" value="<?php echo htmlspecialchars($filterCity); ?>">
                </div>
                <div class="col-md-2 col-sm-4" style="margin-bottom:10px; display:flex; gap:6px;">
                    <button type="submit" class="btn btn-success" style="background:#009146; flex-grow:1; font-weight:700; height:42px;">
                        <i class="fa fa-search"></i> Filter
                    </button>
                    <a href="matrimonial.php" class="btn btn-default" style="height:42px; display:flex; align-items:center;">Reset</a>
                </div>
            </form>
        </div>

        <!-- Privacy Tier Explanatory Banner -->
        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:12px 18px; margin-bottom:25px; font-size:13px; color:#1e40af; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <i class="fa fa-shield"></i> <strong>Multi-Tier Privacy Active:</strong>
                <?php if ($isLoggedIn && (count($myMaleProfiles) > 0 || count($myFemaleProfiles) > 0)): ?>
                    <span style="color:#166534; font-weight:600;">You have an active candidate profile. Reciprocal photographs are unlocked for opposite candidates!</span>
                <?php else: ?>
                    <span>Photographs & direct phone numbers are locked to protect family dignity. Create a candidate profile to unlock photos.</span>
                <?php endif; ?>
            </div>
            <div>
                <?php if (!$isLoggedIn): ?>
                    <a href="user-login.php" style="font-weight:700; color:#1d4ed8; text-decoration:underline;">Login as Member</a>
                <?php elseif (count($myMaleProfiles) === 0 && count($myFemaleProfiles) === 0): ?>
                    <a href="matrimonial-create.php" style="font-weight:700; color:#1d4ed8; text-decoration:underline;">Register Candidate Profile</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Candidate Cards Grid -->
        <div class="row">
            <?php if (empty($profiles)): ?>
                <div class="col-xs-12 text-center" style="padding:60px 20px;">
                    <i class="fa fa-search" style="font-size:48px; color:#cbd5e1; margin-bottom:15px;"></i>
                    <h4 style="color:#64748b; font-weight:600;">No Matrimonial Profiles Found Matching Your Filter</h4>
                    <p style="color:#94a3b8; font-size:13px;">Try clearing filters or search for another city or marital status.</p>
                    <a href="matrimonial.php" class="btn btn-default">Reset All Filters</a>
                </div>
            <?php else: ?>
                <?php foreach ($profiles as $prof): ?>
                    <?php
                        $isOwner = ($isLoggedIn && $currentUserId === intval($prof['created_by_user_id']));
                        
                        // Tier 2 Access Check:
                        // If candidate is female, viewer must have a male profile.
                        // If candidate is male, viewer must have a female profile.
                        $hasReciprocalProfile = false;
                        if ($prof['gender'] === 'female' && count($myMaleProfiles) > 0) {
                            $hasReciprocalProfile = true;
                        } else if ($prof['gender'] === 'male' && count($myFemaleProfiles) > 0) {
                            $hasReciprocalProfile = true;
                        }

                        $isPhotoUnlocked = ($isOwner || $hasReciprocalProfile) && empty($prof['hide_photo_completely']);
                    ?>
                    <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                        <div class="candidate-card">
                            <div>
                                <!-- Header Badge Row -->
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                    <span class="badge" style="background:#009146; font-size:11px; padding:4px 8px;">
                                        <?php echo htmlspecialchars($prof['profile_code'] ?? 'ERQ'); ?>
                                    </span>
                                    <div>
                                        <?php if ($prof['marital_status'] === 'unmarried'): ?>
                                            <span class="badge-marital marital-unmarried">Never Married</span>
                                        <?php elseif ($prof['marital_status'] === 'divorced'): ?>
                                            <span class="badge-marital marital-divorced">Talaq Shuda</span>
                                        <?php elseif ($prof['marital_status'] === 'khula_shuda'): ?>
                                            <span class="badge-marital marital-khula">Khula Shuda</span>
                                        <?php else: ?>
                                            <span class="badge-marital marital-widowed">Bewa</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Photo Area -->
                                <div class="card-photo-wrapper">
                                    <img src="<?php echo !empty($prof['primary_photo']) && file_exists(__DIR__ . '/' . $prof['primary_photo']) ? htmlspecialchars($prof['primary_photo']) : 'images/dummy-avatar.svg'; ?>" class="candidate-photo" alt="Candidate">
                                    <?php if (!$isPhotoUnlocked): ?>
                                        <div class="photo-locked-overlay">
                                            <i class="fa fa-lock" style="font-size:22px; margin-bottom:4px; color:#fcd34d;"></i>
                                            <span style="font-size:10px; font-weight:700; line-height:1.2;">Photo<br>Protected</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Candidate Name & Vital Stats -->
                                <div class="text-center" style="margin-bottom:15px;">
                                    <h4 style="margin:0 0 4px; font-weight:700; color:#1e293b;">
                                        <?php if ($isOwner || $hasReciprocalProfile): ?>
                                            <?php echo htmlspecialchars($prof['full_name']); ?>
                                        <?php else: ?>
                                            <?php echo ($prof['gender'] === 'male' ? 'Dulha Profile' : 'Dulhan Profile') . ' #' . htmlspecialchars($prof['profile_code'] ?? $prof['id']); ?>
                                        <?php endif; ?>
                                    </h4>
                                    <div style="font-size:13px; color:#009146; font-weight:600;">
                                        <?php echo $prof['age']; ?> Yrs • <?php echo htmlspecialchars($prof['height']); ?> • <?php echo htmlspecialchars($prof['cast']); ?>
                                    </div>
                                    <div style="font-size:12px; color:#64748b; margin-top:2px;">
                                        <i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($prof['present_city'] . ', ' . $prof['present_state']); ?>
                                    </div>
                                </div>

                                <!-- Biodata Snapshot -->
                                <div style="background:#f8fafc; border-radius:6px; padding:10px 12px; font-size:12px; margin-bottom:15px; border:1px solid #f1f5f9;">
                                    <div style="margin-bottom:4px;">
                                        <strong style="color:#475569;">🎓 Education:</strong> <?php echo htmlspecialchars($prof['qualification']); ?>
                                    </div>
                                    <div style="margin-bottom:4px;">
                                        <strong style="color:#475569;">💼 Occupation:</strong> <?php echo htmlspecialchars($prof['occupation']); ?>
                                    </div>
                                    <?php if ($isOwner || $hasReciprocalProfile): ?>
                                        <div style="margin-bottom:4px;">
                                            <strong style="color:#475569;">👨 Father:</strong> <?php echo htmlspecialchars($prof['father_occupation'] ?: 'Respected Citizen'); ?>
                                        </div>
                                    <?php else: ?>
                                        <div style="color:#94a3b8; font-style:italic;">
                                            🔒 Family & photo unlocked for reciprocal profiles
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Action Footer -->
                            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f1f5f9; padding-top:12px; gap:8px;">
                                <a href="matrimonial-profile-view.php?id=<?php echo $prof['id']; ?>" class="btn-card-action btn-view-profile">
                                    <i class="fa fa-eye"></i> View Biodata
                                </a>

                                <?php if ($isLoggedIn && !$isOwner): ?>
                                    <?php
                                        // Pick candidate profile to propose from
                                        $eligibleSenders = ($prof['gender'] === 'female') ? $myMaleProfiles : $myFemaleProfiles;
                                    ?>
                                    <?php if (count($eligibleSenders) > 0): ?>
                                        <form method="POST" action="matrimonial.php" style="display:inline;">
                                            <input type="hidden" name="action" value="send_interest">
                                            <input type="hidden" name="target_profile_id" value="<?php echo $prof['id']; ?>">
                                            <input type="hidden" name="sender_profile_id" value="<?php echo $eligibleSenders[0]['id']; ?>">
                                            <button type="submit" class="btn-card-action btn-interest">
                                                <i class="fa fa-heart"></i> Show Interest
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="matrimonial-create.php" class="btn-card-action btn-interest" title="Create reciprocal profile to show interest">
                                            <i class="fa fa-heart-o"></i> Show Interest
                                        </a>
                                    <?php endif; ?>
                                <?php elseif (!$isLoggedIn): ?>
                                    <a href="user-login.php" class="btn-card-action btn-interest">
                                        <i class="fa fa-heart-o"></i> Show Interest
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- Footer -->
    <footer style="background:#0f172a; color:#94a3b8; padding:30px 0; margin-top:50px; font-size:13px;">
        <div class="container text-center">
            <p style="margin-bottom:8px;">&copy; <?php echo date('Y'); ?> <strong>Anjuman Eraquee INDIA</strong> — Kalal Eraquee Community Matrimonial Directory</p>
            <p style="margin:0; font-size:12px;">All profiles are submitted by verified registered members. Privacy and dignity are strictly protected.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>

