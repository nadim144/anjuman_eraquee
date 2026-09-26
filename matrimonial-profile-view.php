<?php
session_start();
require_once __DIR__ . '/db.php';
$conn = get_db_connection();

$profileId = intval($_GET['id'] ?? 0);
if ($profileId <= 0 || !$conn) {
    header('Location: matrimonial.php');
    exit;
}

$isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
$currentUserId = intval($_SESSION['user_id'] ?? 0);
$isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Fetch Profile
$res = mysqli_query($conn, "SELECT * FROM matrimonial_profiles WHERE id = $profileId LIMIT 1");
if (!$res || mysqli_num_rows($res) === 0) {
    header('Location: matrimonial.php');
    exit;
}
$profile = mysqli_fetch_assoc($res);

// Determine Viewer's Tier
$isOwner = ($isLoggedIn && $currentUserId === intval($profile['created_by_user_id']));
$isTier3Approved = false;

// Check if user has an approved contact request
if ($isLoggedIn && !$isOwner) {
    $reqCheck = mysqli_query($conn, "SELECT id, status FROM matrimonial_access_requests WHERE requester_user_id = $currentUserId AND target_profile_id = $profileId AND status = 'approved_by_admin' LIMIT 1");
    if ($reqCheck && mysqli_num_rows($reqCheck) > 0) {
        $isTier3Approved = true;
    }
}

// Check Reciprocal Profile (Tier 2)
$hasReciprocalProfile = false;
$myOppositeProfiles = [];
if ($isLoggedIn && !$isOwner) {
    $neededGender = ($profile['gender'] === 'female') ? 'male' : 'female';
    $oppRes = mysqli_query($conn, "SELECT id, profile_code, full_name FROM matrimonial_profiles WHERE created_by_user_id = $currentUserId AND gender = '$neededGender' AND status = 'active'");
    if ($oppRes && mysqli_num_rows($oppRes) > 0) {
        $hasReciprocalProfile = true;
        while ($op = mysqli_fetch_assoc($oppRes)) {
            $myOppositeProfiles[] = $op;
        }
    }
}

// Access Levels:
// Level 3 (Full): Owner OR Admin OR Approved Request
// Level 2 (Intermediate): Has reciprocal profile
// Level 1 (Restricted): Guest or no reciprocal profile
$accessLevel = 1;
if ($isOwner || $isAdmin || $isTier3Approved) {
    $accessLevel = 3;
} else if ($hasReciprocalProfile) {
    $accessLevel = 2;
}

$photoUnlocked = ($accessLevel >= 2) && empty($profile['hide_photo_completely']);
if ($accessLevel === 3) {
    $photoUnlocked = true;
}

// Handle Direct Post Request for Contact, Interest, or Admin Approval
$feedbackMsg = '';
$feedbackType = 'info';

if (isset($_GET['updated'])) {
    if ($profile['status'] === 'pending_approval') {
        $feedbackMsg = "✏️ Profile biodata successfully updated! Your modifications have been submitted to Admin / Super Admin for verification and will appear in the directory once approved.";
        $feedbackType = 'success';
    } else {
        $feedbackMsg = "✏️ Profile biodata successfully updated!";
        $feedbackType = 'success';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Admin 1-Click Profile Approval
    if ($isAdmin && $action === 'admin_approve_profile') {
        $adminId = intval($_SESSION['admin_id'] ?? 1);
        $upd = mysqli_query($conn, "UPDATE matrimonial_profiles SET status = 'active', updated_at = NOW() WHERE id = $profileId");
        if ($upd) {
            $profile['status'] = 'active';
            $feedbackMsg = "✅ Profile #" . htmlspecialchars($profile['profile_code']) . " approved and published live to directory!";
            $feedbackType = 'success';
        }
    }
    // Admin Reject/Pause Profile
    else if ($isAdmin && $action === 'admin_reject_profile') {
        $upd = mysqli_query($conn, "UPDATE matrimonial_profiles SET status = 'hidden', updated_at = NOW() WHERE id = $profileId");
        if ($upd) {
            $profile['status'] = 'hidden';
            $feedbackMsg = "Profile #" . htmlspecialchars($profile['profile_code']) . " status set to Hidden/Suspended.";
            $feedbackType = 'warning';
        }
    }
    // Request Contact from Admin
    else if ($action === 'request_contact_admin' && $isLoggedIn) {
        $selectedMyProfile = intval($_POST['my_profile_id'] ?? 0);
        $chk = mysqli_query($conn, "SELECT id, status FROM matrimonial_access_requests WHERE requester_user_id = $currentUserId AND target_profile_id = $profileId LIMIT 1");
        if ($chk && mysqli_num_rows($chk) > 0) {
            $feedbackMsg = "You have already submitted a request for this candidate's contact details. Admin review is pending.";
            $feedbackType = 'warning';
        } else {
            $ins = mysqli_query($conn, "INSERT INTO matrimonial_access_requests (requester_user_id, target_profile_id, requester_profile_id, status) VALUES ($currentUserId, $profileId, $selectedMyProfile, 'pending')");
            if ($ins) {
                $feedbackMsg = "Contact Access Request submitted to Anjuman Admin! Once verified, direct phone and address details will unlock here.";
                $feedbackType = 'success';
            }
        }
    }

    // Send Interest
    else if ($action === 'send_interest' && $isLoggedIn) {
        $selectedMyProfile = intval($_POST['my_profile_id'] ?? 0);
        $note = trim(mysqli_real_escape_string($conn, $_POST['note'] ?? ''));
        $targetUserId = intval($profile['created_by_user_id']);

        if ($selectedMyProfile <= 0) {
            $feedbackMsg = "Please select which of your profiles is expressing interest.";
            $feedbackType = 'danger';
        } else {
            $chk = mysqli_query($conn, "SELECT id FROM matrimonial_interests WHERE sender_profile_id = $selectedMyProfile AND receiver_profile_id = $profileId");
            if ($chk && mysqli_num_rows($chk) > 0) {
                $feedbackMsg = "You have already sent an interest to this candidate profile.";
                $feedbackType = 'warning';
            } else {
                $ins = mysqli_query($conn, "INSERT INTO matrimonial_interests (sender_user_id, sender_profile_id, receiver_user_id, receiver_profile_id, message, status) VALUES ($currentUserId, $selectedMyProfile, $targetUserId, $profileId, '$note', 'pending')");
                if ($ins) {
                    $feedbackMsg = "❤️ Interest sent successfully! The candidate's family has been notified in their dashboard.";
                    $feedbackType = 'success';
                }
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
    <title><?php echo htmlspecialchars($profile['profile_code']); ?> Biodata | Anjuman Eraquee INDIA</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="images/icon/tabicon.jpeg" type="image/gif">
    <style>
        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: 'Droid Sans', sans-serif;
        }
        .biodata-wrapper {
            max-width: 950px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .biodata-header {
            background: linear-gradient(135deg, #009146 0%, #006b33 100%);
            color: #ffffff;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .profile-hero {
            display: flex;
            align-items: center;
            gap: 25px;
            padding: 30px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            flex-wrap: wrap;
        }
        .hero-photo-box {
            position: relative;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            border: 4px solid #009146;
            overflow: hidden;
            background: #f1f5f9;
            flex-shrink: 0;
            margin: 0 auto;
        }
        .hero-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-blur-cover {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(8px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            text-align: center;
            padding: 10px;
        }
        .section-header {
            font-size: 16px;
            font-weight: 700;
            color: #009146;
            margin: 25px 0 15px;
            padding-bottom: 6px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }
        .data-item {
            background: #f8fafc;
            padding: 10px 14px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .data-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
        }
        .data-val {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            margin-top: 2px;
        }
        .protected-box {
            background: #f0fdf4;
            border: 2px dashed #86efac;
            border-radius: 8px;
            padding: 20px;
            margin-top: 25px;
        }
    </style>
</head>
<body>

    <div class="container" style="padding-top:20px; padding-bottom:50px;">
        
        <div style="margin-bottom:15px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <a href="matrimonial.php" class="btn btn-default btn-sm" style="font-weight:600; border-radius:20px;">
                <i class="fa fa-arrow-left"></i> Back to Directory
            </a>
            <div style="display:flex; gap:8px; align-items:center;">
                <?php if ($isOwner || $isAdmin): ?>
                    <a href="matrimonial-edit.php?id=<?php echo $profile['id']; ?>" class="btn btn-warning btn-sm" style="background:#f59e0b; border:none; font-weight:700; border-radius:20px; color:#ffffff;">
                        <i class="fa fa-pencil"></i> Edit Profile Details
                    </a>
                <?php endif; ?>
                <?php if ($isLoggedIn): ?>
                    <a href="matrimonial-manage.php" class="btn btn-default btn-sm" style="font-weight:600; border-radius:20px;">
                        💍 My Matrimonial Hub
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($feedbackMsg)): ?>
            <div class="alert alert-<?php echo $feedbackType; ?>" style="border-radius:8px; font-weight:600;">
                <?php echo $feedbackMsg; ?>
            </div>
        <?php endif; ?>

        <?php if ($profile['status'] === 'pending_approval'): ?>
            <div style="background:#fffbeb; border:1px solid #fde68a; border-left:5px solid #f59e0b; padding:16px 20px; border-radius:8px; margin-bottom:20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                    <div>
                        <div style="font-weight:700; color:#b45309; font-size:15px;">
                            <i class="fa fa-clock-o"></i> Status: Under Admin Review (Pending Approval)
                        </div>
                        <div style="font-size:13px; color:#92400e; margin-top:3px;">
                            This profile was created or modified and is awaiting verification by Admin / Super Admin before appearing live in the public directory.
                        </div>
                    </div>
                    <?php if ($isAdmin): ?>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="admin_approve_profile">
                                <button type="submit" class="btn btn-success btn-sm" style="background:#009146; font-weight:700; border-radius:6px;">
                                    <i class="fa fa-check-circle"></i> Approve & Publish
                                </button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="admin_reject_profile">
                                <button type="submit" class="btn btn-danger btn-sm" style="border-radius:6px;" onclick="return confirm('Suspend/hide this profile?');">
                                    <i class="fa fa-times"></i> Reject / Pause
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="biodata-wrapper">
            <!-- Header Banner -->
            <div class="biodata-header">
                <div>
                    <span class="badge" style="background:#e5ae49; color:#0f172a; font-size:13px; font-weight:800; padding:5px 12px;">
                        <?php echo htmlspecialchars($profile['profile_code']); ?>
                    </span>
                    <span style="font-size:13px; margin-left:8px; opacity:0.9;">
                        <?php echo ($profile['gender'] === 'male') ? 'Dulha (Groom Profile)' : 'Dulhan (Bride Profile)'; ?>
                    </span>
                    <h2 style="margin:6px 0 0; font-size:24px; font-weight:800;">
                        <?php if ($accessLevel >= 2): ?>
                            <?php echo htmlspecialchars($profile['full_name']); ?>
                        <?php else: ?>
                            <?php echo ($profile['gender'] === 'male' ? 'Dulha Candidate' : 'Dulhan Candidate') . ' #' . htmlspecialchars($profile['profile_code']); ?>
                        <?php endif; ?>
                    </h2>
                </div>
                <div>
                    <?php if ($profile['status'] === 'pending_approval'): ?>
                        <span class="badge" style="background:#f59e0b; font-size:13px; padding:6px 12px; margin-right:4px;">⏳ Pending Approval</span>
                    <?php endif; ?>
                    <?php if ($profile['marital_status'] === 'unmarried'): ?>
                        <span class="badge" style="background:#22c55e; font-size:13px; padding:6px 12px;">Never Married</span>
                    <?php elseif ($profile['marital_status'] === 'divorced'): ?>
                        <span class="badge" style="background:#f59e0b; font-size:13px; padding:6px 12px;">Talaq Shuda</span>
                    <?php elseif ($profile['marital_status'] === 'khula_shuda'): ?>
                        <span class="badge" style="background:#ef4444; font-size:13px; padding:6px 12px;">Khula Shuda</span>
                    <?php else: ?>
                        <span class="badge" style="background:#3b82f6; font-size:13px; padding:6px 12px;">Bewa (Widowed)</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Hero Section with Photo & Highlights -->
            <div class="profile-hero">
                <div class="hero-photo-box">
                    <img src="<?php echo !empty($profile['primary_photo']) && file_exists(__DIR__ . '/' . $profile['primary_photo']) ? htmlspecialchars($profile['primary_photo']) : 'images/dummy-avatar.svg'; ?>" class="hero-photo" alt="Candidate Photo">
                    <?php if (!$photoUnlocked): ?>
                        <div class="photo-blur-cover">
                            <i class="fa fa-lock" style="font-size:32px; color:#fcd34d; margin-bottom:6px;"></i>
                            <span style="font-size:11px; font-weight:700;">Photo Protected</span>
                            <small style="font-size:9px; opacity:0.85; margin-top:3px;">Create reciprocal candidate profile to unlock</small>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="flex-grow:1;">
                    <div style="font-size:16px; font-weight:700; color:#009146; margin-bottom:6px;">
                        <?php echo $profile['age']; ?> Years • <?php echo htmlspecialchars($profile['height']); ?> • <?php echo htmlspecialchars($profile['complexion']); ?> Complexion
                    </div>
                    <div style="font-size:14px; color:#475569; margin-bottom:4px;">
                        <strong>🎓 Qualification:</strong> <?php echo htmlspecialchars($profile['qualification']); ?>
                    </div>
                    <div style="font-size:14px; color:#475569; margin-bottom:4px;">
                        <strong>💼 Profession:</strong> <?php echo htmlspecialchars($profile['occupation']); ?> (<?php echo htmlspecialchars($profile['employed_in']); ?>)
                    </div>
                    <div style="font-size:14px; color:#475569;">
                        <strong>📍 Current City:</strong> <?php echo htmlspecialchars($profile['present_city'] . ', ' . $profile['present_state']); ?>
                    </div>

                    <!-- Interest & Contact Action Bar -->
                    <div style="margin-top:20px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                        <?php if ($isOwner || $isAdmin): ?>
                            <a href="matrimonial-edit.php?id=<?php echo $profile['id']; ?>" class="btn btn-warning" style="background:#f59e0b; border:none; font-weight:700; border-radius:6px; padding:8px 18px; color:#ffffff;">
                                <i class="fa fa-pencil"></i> Edit Biodata
                            </a>
                        <?php endif; ?>

                        <?php if ($isLoggedIn && !$isOwner): ?>
                            <!-- Show Interest Button -->
                            <?php if ($hasReciprocalProfile): ?>
                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#interestModal" style="background:#db2777; border:none; font-weight:700; border-radius:6px; padding:8px 18px;">
                                    <i class="fa fa-heart"></i> Show Interest / Proposal
                                </button>
                            <?php else: ?>
                                <a href="matrimonial-create.php" class="btn btn-default" style="font-weight:600; border-radius:6px;" title="Register candidate profile to show interest">
                                    <i class="fa fa-heart-o"></i> Register Profile to Show Interest
                                </a>
                            <?php endif; ?>

                            <!-- Request Contact Button -->
                            <?php if ($accessLevel < 3): ?>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#contactModal" style="background:#0284c7; border:none; font-weight:700; border-radius:6px; padding:8px 18px;">
                                    <i class="fa fa-phone"></i> Request Full Contact via Admin
                                </button>
                            <?php endif; ?>
                        <?php elseif (!$isLoggedIn): ?>
                            <a href="user-login.php?redirect=matrimonial-profile-view.php?id=<?php echo $profile['id']; ?>" class="btn btn-success" style="background:#009146; border:none; font-weight:700;">
                                <i class="fa fa-sign-in"></i> Login to Connect with Family
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Full Details Canvas -->
            <div style="padding: 25px 30px;">
                
                <!-- 1. Basic & Personal Details -->
                <div class="section-header">
                    <i class="fa fa-info-circle"></i> 1. Personal & Religious Background
                </div>
                <div class="data-grid">
                    <div class="data-item">
                        <div class="data-label">Full Name</div>
                        <div class="data-val"><?php echo ($accessLevel >= 2) ? htmlspecialchars($profile['full_name']) : 'Protected (Tier 1)'; ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Age & DOB</div>
                        <div class="data-val"><?php echo $profile['age']; ?> Years <?php echo ($accessLevel === 3 && !empty($profile['dob'])) ? '(' . htmlspecialchars($profile['dob']) . ')' : ''; ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Height</div>
                        <div class="data-val"><?php echo htmlspecialchars($profile['height']); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Marital Status</div>
                        <div class="data-val"><?php echo ucfirst(str_replace('_', ' ', $profile['marital_status'])); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Community Caste</div>
                        <div class="data-val"><?php echo htmlspecialchars($profile['cast']); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Sect & Mother Tongue</div>
                        <div class="data-val"><?php echo htmlspecialchars($profile['sect']); ?> • <?php echo htmlspecialchars($profile['mother_tongue']); ?></div>
                    </div>
                </div>

                <!-- 2. Education & Professional Background -->
                <div class="section-header">
                    <i class="fa fa-graduation-cap"></i> 2. Education & Career
                </div>
                <div class="data-grid">
                    <div class="data-item">
                        <div class="data-label">Highest Education</div>
                        <div class="data-val"><?php echo htmlspecialchars($profile['qualification']); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Occupation</div>
                        <div class="data-val"><?php echo htmlspecialchars($profile['occupation']); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Employed In</div>
                        <div class="data-val"><?php echo htmlspecialchars($profile['employed_in']); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Annual Income</div>
                        <div class="data-val"><?php echo !empty($profile['annual_income']) ? htmlspecialchars($profile['annual_income']) : 'Not Disclosed'; ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Work Location</div>
                        <div class="data-val"><?php echo htmlspecialchars($profile['work_city'] ?: $profile['present_city']); ?></div>
                    </div>
                </div>

                <!-- 3. Family Background -->
                <div class="section-header">
                    <i class="fa fa-users"></i> 3. Family Background
                </div>
                <div class="data-grid">
                    <div class="data-item">
                        <div class="data-label">Father's Name</div>
                        <div class="data-val"><?php echo ($accessLevel >= 2) ? htmlspecialchars($profile['father_name'] ?: '-') : 'Protected (Tier 1)'; ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Father's Profession</div>
                        <div class="data-val"><?php echo htmlspecialchars($profile['father_occupation'] ?: '-'); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Mother's Name</div>
                        <div class="data-val"><?php echo ($accessLevel >= 2) ? htmlspecialchars($profile['mother_name'] ?: '-') : 'Protected (Tier 1)'; ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Brothers & Sisters</div>
                        <div class="data-val"><?php echo $profile['brothers_count']; ?> Brothers, <?php echo $profile['sisters_count']; ?> Sisters</div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Family Values & Type</div>
                        <div class="data-val"><?php echo htmlspecialchars($profile['family_values']); ?> • <?php echo htmlspecialchars($profile['family_type']); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Native Place (Ancestral)</div>
                        <div class="data-val"><?php echo htmlspecialchars($profile['native_place'] ?: '-'); ?></div>
                    </div>
                </div>

                <!-- 4. Expectations & Remarks -->
                <?php if (!empty($profile['partner_preferences']) || !empty($profile['about_candidate'])): ?>
                    <div class="section-header">
                        <i class="fa fa-heart"></i> 4. Expectations & About Candidate
                    </div>
                    <div class="row">
                        <?php if (!empty($profile['partner_preferences'])): ?>
                            <div class="col-md-6" style="margin-bottom:12px;">
                                <div class="data-item" style="height:100%;">
                                    <div class="data-label">Partner Expectations</div>
                                    <div style="font-size:13px; color:#1e293b; margin-top:4px;">
                                        <?php echo nl2br(htmlspecialchars($profile['partner_preferences'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($profile['about_candidate'])): ?>
                            <div class="col-md-6" style="margin-bottom:12px;">
                                <div class="data-item" style="height:100%;">
                                    <div class="data-label">About Candidate</div>
                                    <div style="font-size:13px; color:#1e293b; margin-top:4px;">
                                        <?php echo nl2br(htmlspecialchars($profile['about_candidate'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- 5. Direct Contact & Full Address (Protected Tier 3 Box) -->
                <div class="section-header">
                    <i class="fa fa-address-card"></i> 5. Family Contact Information
                </div>
                <?php if ($accessLevel === 3): ?>
                    <!-- UNLOCKED CONTACT DETAILS -->
                    <div class="protected-box">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                            <h4 style="margin:0; color:#166534; font-weight:700;">
                                <i class="fa fa-check-circle"></i> Direct Contact Details (Unlocked)
                            </h4>
                            <span class="badge" style="background:#166534;">Verified & Approved</span>
                        </div>
                        <div class="row">
                            <div class="col-md-6" style="margin-bottom:8px;">
                                <strong>👤 Contact Person:</strong> <?php echo htmlspecialchars($profile['contact_person_name']); ?> (<?php echo htmlspecialchars($profile['contact_relation']); ?>)
                            </div>
                            <div class="col-md-6" style="margin-bottom:8px;">
                                <strong>📞 Mobile Phone:</strong> <a href="tel:<?php echo htmlspecialchars($profile['contact_phone']); ?>" style="color:#009146; font-weight:700; font-size:15px;"><?php echo htmlspecialchars($profile['contact_phone']); ?></a>
                            </div>
                            <?php if (!empty($profile['contact_whatsapp'])): ?>
                                <div class="col-md-6" style="margin-bottom:8px;">
                                    <strong>💬 WhatsApp:</strong> <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $profile['contact_whatsapp']); ?>" target="_blank" style="color:#16a34a; font-weight:700;"><?php echo htmlspecialchars($profile['contact_whatsapp']); ?></a>
                                </div>
                            <?php endif; ?>
                            <div class="col-md-12" style="margin-top:8px;">
                                <strong>📍 Full Residential Address:</strong> <?php echo htmlspecialchars($profile['full_address'] ?: ($profile['present_city'] . ', ' . $profile['present_state'])); ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- LOCKED CONTACT DETAILS (PROMPT TO REQUEST ADMIN UNLOCK) -->
                    <div style="background:#f1f5f9; border:1px solid #cbd5e1; border-radius:8px; padding:25px; text-align:center;">
                        <i class="fa fa-lock" style="font-size:32px; color:#64748b; margin-bottom:8px;"></i>
                        <h4 style="margin:0 0 6px; font-weight:700; color:#334155;">Direct Contact Numbers & Residential Address are Protected</h4>
                        <p style="color:#64748b; font-size:13px; max-width:540px; margin:0 auto 15px;">
                            To safeguard family privacy and prevent unsolicited contact, guardian telephone numbers and addresses are released only after formal review and Admin approval.
                        </p>
                        <?php if ($isLoggedIn): ?>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#contactModal" style="background:#0284c7; border:none; font-weight:700; padding:10px 24px; border-radius:6px;">
                                <i class="fa fa-phone"></i> Request Full Contact Exchange
                            </button>
                        <?php else: ?>
                            <a href="user-login.php?redirect=matrimonial-profile-view.php?id=<?php echo $profile['id']; ?>" class="btn btn-success" style="background:#009146; border:none; font-weight:700;">
                                Login to Request Contact Details
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- MODAL: Show Interest -->
    <?php if ($isLoggedIn && $hasReciprocalProfile): ?>
        <div class="modal fade" id="interestModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content" style="border-radius:10px; overflow:hidden;">
                    <div class="modal-header" style="background:#db2777; color:#fff;">
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                        <h4 class="modal-title" style="font-weight:700;"><i class="fa fa-heart"></i> Send Proposal Interest</h4>
                    </div>
                    <form method="POST" action="matrimonial-profile-view.php?id=<?php echo $profile['id']; ?>">
                        <input type="hidden" name="action" value="send_interest">
                        <div class="modal-body" style="padding:20px;">
                            <p style="font-size:13px; color:#475569;">
                                Sending interest to candidate <strong><?php echo htmlspecialchars($profile['profile_code']); ?></strong>. Their family will receive a notification in their dashboard.
                            </p>
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:700;">Select Your Candidate Profile *</label>
                                <select name="my_profile_id" class="form-control" required>
                                    <?php foreach ($myOppositeProfiles as $mop): ?>
                                        <option value="<?php echo $mop['id']; ?>">
                                            <?php echo htmlspecialchars($mop['full_name']); ?> (<?php echo htmlspecialchars($mop['profile_code']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:700;">Optional Message to Family</label>
                                <textarea name="note" class="form-control" rows="3" placeholder="e.g. We liked your biodata and family background and would like to explore this alliance."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer" style="background:#f8fafc;">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger" style="background:#db2777; border:none; font-weight:700;">
                                <i class="fa fa-paper-plane"></i> Send Interest Now
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- MODAL: Request Full Contact from Admin -->
    <?php if ($isLoggedIn): ?>
        <div class="modal fade" id="contactModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content" style="border-radius:10px; overflow:hidden;">
                    <div class="modal-header" style="background:#0284c7; color:#fff;">
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                        <h4 class="modal-title" style="font-weight:700;"><i class="fa fa-phone"></i> Request Full Contact Exchange</h4>
                    </div>
                    <form method="POST" action="matrimonial-profile-view.php?id=<?php echo $profile['id']; ?>">
                        <input type="hidden" name="action" value="request_contact_admin">
                        <div class="modal-body" style="padding:20px;">
                            <p style="font-size:13px; color:#475569;">
                                Request verified guardian contact numbers and residential address for Profile <strong>#<?php echo htmlspecialchars($profile['profile_code']); ?></strong>.
                            </p>
                            <?php if (count($myOppositeProfiles) > 0): ?>
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:700;">On Behalf Of Your Candidate Profile *</label>
                                    <select name="my_profile_id" class="form-control" required>
                                        <?php foreach ($myOppositeProfiles as $mop): ?>
                                            <option value="<?php echo $mop['id']; ?>">
                                                <?php echo htmlspecialchars($mop['full_name']); ?> (<?php echo htmlspecialchars($mop['profile_code']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            <div class="alert alert-info" style="font-size:12px; margin-bottom:0;">
                                <i class="fa fa-info-circle"></i> Once the Anjuman Super Admin verifies the alliance request, the family contact details will automatically be unlocked for you.
                            </div>
                        </div>
                        <div class="modal-footer" style="background:#f8fafc;">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" style="background:#0284c7; border:none; font-weight:700;">
                                <i class="fa fa-send"></i> Submit Request to Admin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>

