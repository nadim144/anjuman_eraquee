<?php
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: user-login.php?redirect=matrimonial-manage.php');
    exit;
}

require_once __DIR__ . '/db.php';
$conn = get_db_connection();

$userId = intval($_SESSION['user_id'] ?? 0);
$feedbackMsg = '';
$feedbackType = 'success';

if (isset($_GET['created'])) {
    $feedbackMsg = "New matrimonial profile registered successfully!";
    $feedbackType = "success";
}
if (isset($_GET['updated'])) {
    $feedbackMsg = "Candidate biodata updated successfully! Modifications have been submitted to Admin / Super Admin for approval.";
    $feedbackType = "success";
}

// Handle Actions (Accept/Decline Interest, Toggle Profile Status)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    $action = $_POST['action'] ?? '';

    // ACCEPT INTEREST
    if ($action === 'accept_interest') {
        $interestId = intval($_POST['interest_id'] ?? 0);
        $upd = mysqli_query($conn, "UPDATE matrimonial_interests SET status = 'accepted', responded_at = NOW() WHERE id = $interestId AND receiver_user_id = $userId");
        if ($upd) {
            $feedbackMsg = "Interest accepted! Mutual interest established. You or the other family can now request full contact exchange via Admin.";
            $feedbackType = 'success';
        }
    }

    // DECLINE INTEREST
    else if ($action === 'decline_interest') {
        $interestId = intval($_POST['interest_id'] ?? 0);
        $upd = mysqli_query($conn, "UPDATE matrimonial_interests SET status = 'declined', responded_at = NOW() WHERE id = $interestId AND receiver_user_id = $userId");
        if ($upd) {
            $feedbackMsg = "Interest declined politely.";
            $feedbackType = 'info';
        }
    }

    // REQUEST FULL CONTACT FROM ADMIN
    else if ($action === 'request_admin_contact') {
        $targetProfileId = intval($_POST['target_profile_id'] ?? 0);
        $myProfileId = intval($_POST['my_profile_id'] ?? 0);

        if ($targetProfileId > 0 && $myProfileId > 0) {
            // Check if already requested
            $chk = mysqli_query($conn, "SELECT id, status FROM matrimonial_access_requests WHERE requester_user_id = $userId AND target_profile_id = $targetProfileId LIMIT 1");
            if ($chk && mysqli_num_rows($chk) > 0) {
                $feedbackMsg = "You have already requested contact details for this profile. Admin review is in progress.";
                $feedbackType = 'warning';
            } else {
                $ins = mysqli_query($conn, "INSERT INTO matrimonial_access_requests (requester_user_id, target_profile_id, requester_profile_id, status) VALUES ($userId, $targetProfileId, $myProfileId, 'pending')");
                if ($ins) {
                    $feedbackMsg = "Full Contact Request sent to Anjuman Admin! Once approved, the family's direct phone and address will be unlocked here.";
                    $feedbackType = 'success';
                }
            }
        }
    }

    // TOGGLE PROFILE STATUS (Active / Married / Hidden)
    else if ($action === 'toggle_profile_status') {
        $profileId = intval($_POST['profile_id'] ?? 0);
        $newStatus = mysqli_real_escape_string($conn, $_POST['new_status'] ?? 'active');
        if (in_array($newStatus, ['active', 'hidden', 'married'])) {
            mysqli_query($conn, "UPDATE matrimonial_profiles SET status = '$newStatus', updated_at = NOW() WHERE id = $profileId AND created_by_user_id = $userId");
            $feedbackMsg = "Profile status updated to " . ucfirst($newStatus) . ".";
            $feedbackType = 'success';
        }
    }
}

// Fetch user's created profiles
$myProfiles = [];
if ($conn && $userId > 0) {
    $res = mysqli_query($conn, "SELECT * FROM matrimonial_profiles WHERE created_by_user_id = $userId ORDER BY id DESC");
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $myProfiles[] = $r;
        }
    }
}

// Fetch Received Interests
$receivedInterests = [];
if ($conn && $userId > 0) {
    $rSql = "SELECT i.*, 
                    sp.profile_code AS sender_code, sp.full_name AS sender_name, sp.gender AS sender_gender,
                    sp.age AS sender_age, sp.qualification AS sender_qual, sp.occupation AS sender_occup,
                    sp.present_city AS sender_city, sp.primary_photo AS sender_photo, sp.hide_photo_completely,
                    tp.full_name AS my_candidate_name, tp.profile_code AS my_candidate_code
             FROM matrimonial_interests i
             JOIN matrimonial_profiles sp ON i.sender_profile_id = sp.id
             JOIN matrimonial_profiles tp ON i.receiver_profile_id = tp.id
             WHERE i.receiver_user_id = $userId
             ORDER BY i.id DESC";
    $rRes = mysqli_query($conn, $rSql);
    if ($rRes) {
        while ($row = mysqli_fetch_assoc($rRes)) {
            $receivedInterests[] = $row;
        }
    }
}

// Fetch Sent Interests
$sentInterests = [];
if ($conn && $userId > 0) {
    $sSql = "SELECT i.*, 
                    tp.id AS target_id, tp.profile_code AS target_code, tp.gender AS target_gender,
                    tp.age AS target_age, tp.qualification AS target_qual, tp.occupation AS target_occup,
                    tp.present_city AS target_city, tp.primary_photo AS target_photo, tp.hide_photo_completely,
                    sp.full_name AS my_candidate_name
             FROM matrimonial_interests i
             JOIN matrimonial_profiles tp ON i.receiver_profile_id = tp.id
             JOIN matrimonial_profiles sp ON i.sender_profile_id = sp.id
             WHERE i.sender_user_id = $userId
             ORDER BY i.id DESC";
    $sRes = mysqli_query($conn, $sSql);
    if ($sRes) {
        while ($row = mysqli_fetch_assoc($sRes)) {
            $sentInterests[] = $row;
        }
    }
}

// Fetch Approved Contact Unlocks
$approvedContacts = [];
if ($conn && $userId > 0) {
    $appSql = "SELECT ar.*, p.*, ar.status AS request_status
               FROM matrimonial_access_requests ar
               JOIN matrimonial_profiles p ON ar.target_profile_id = p.id
               WHERE ar.requester_user_id = $userId AND ar.status = 'approved_by_admin'
               ORDER BY ar.reviewed_at DESC";
    $appRes = mysqli_query($conn, $appSql);
    if ($appRes) {
        while ($row = mysqli_fetch_assoc($appRes)) {
            $approvedContacts[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matrimonial Command Center | Anjuman Eraquee INDIA</title>
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
        .nav-tabs-custom {
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 24px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .nav-tabs-custom li a {
            border: none !important;
            color: #64748b;
            font-weight: 600;
            font-size: 14px;
            padding: 12px 20px;
            border-radius: 8px 8px 0 0;
            background: #f1f5f9;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .nav-tabs-custom li.active a, .nav-tabs-custom li a:hover {
            background: #009146 !important;
            color: #ffffff !important;
        }
        .card-box {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
            padding: 20px;
            margin-bottom: 20px;
        }
        .profile-thumb {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            border: 2px solid #009146;
            object-fit: cover;
            background: #f1f5f9;
        }
        .badge-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
        }
        .badge-active { background: #dcfce7; color: #15803d; }
        .badge-pending { background: #fef3c7; color: #b45309; }
        .badge-married { background: #e0f2fe; color: #0369a1; }
        .badge-declined { background: #fee2e2; color: #b91c1c; }
        .btn-action-sm {
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="dashboard-header">
        <div class="container">
            <div class="row" style="display:flex; align-items:center; justify-content:space-between;">
                <div class="col-xs-8">
                    <h2 style="margin:0; font-size:20px; font-weight:700;">💍 Matrimonial Center</h2>
                    <small style="opacity:0.9;">Manage Your Candidate Profiles, Interests & Verified Alliances</small>
                </div>
                <div class="col-xs-4 text-right" style="display:flex; gap:8px; justify-content:flex-end;">
                    <a href="matrimonial.php" class="btn btn-default btn-sm" style="font-weight:600; border-radius:20px;">
                        🔍 Browse Alliances
                    </a>
                    <a href="user-dashboard.php" class="btn btn-default btn-sm" style="font-weight:600; border-radius:20px;">
                        <i class="fa fa-dashboard"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="margin-top:25px; margin-bottom:50px;">
        
        <?php if (!empty($feedbackMsg)): ?>
            <div class="alert alert-<?php echo $feedbackType; ?>" style="border-radius:6px; font-weight:600;">
                <?php echo htmlspecialchars($feedbackMsg); ?>
            </div>
        <?php endif; ?>

        <!-- Quick Summary & Actions -->
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:20px; gap:12px;">
            <div>
                <h3 style="margin:0; font-weight:700; color:#1e293b;">My Matrimonial Hub</h3>
                <p style="margin:0; color:#64748b; font-size:13px;">You have created <strong><?php echo count($myProfiles); ?></strong> profile(s).</p>
            </div>
            <div>
                <a href="matrimonial-create.php" class="btn btn-success" style="background:#009146; border:none; padding:10px 20px; font-weight:700; border-radius:6px;">
                    <i class="fa fa-plus-circle"></i> Create New Candidate Profile
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs-custom" role="tablist">
            <li role="presentation" class="active">
                <a href="#my_profiles" aria-controls="my_profiles" role="tab" data-toggle="tab">
                    <i class="fa fa-users"></i> My Candidate Profiles (<?php echo count($myProfiles); ?>)
                </a>
            </li>
            <li role="presentation">
                <a href="#received_interests" aria-controls="received_interests" role="tab" data-toggle="tab">
                    <i class="fa fa-inbox"></i> Received Interests 
                    <?php if (count($receivedInterests) > 0): ?>
                        <span class="badge" style="background:#dc2626; font-size:11px;"><?php echo count($receivedInterests); ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li role="presentation">
                <a href="#sent_interests" aria-controls="sent_interests" role="tab" data-toggle="tab">
                    <i class="fa fa-paper-plane"></i> Sent Interests (<?php echo count($sentInterests); ?>)
                </a>
            </li>
            <li role="presentation">
                <a href="#approved_contacts" aria-controls="approved_contacts" role="tab" data-toggle="tab">
                    <i class="fa fa-unlock-alt"></i> Unlocked Contacts (<?php echo count($approvedContacts); ?>)
                </a>
            </li>
        </ul>

        <!-- Tab Contents -->
        <div class="tab-content">

            <!-- TAB 1: My Profiles -->
            <div role="tabpanel" class="tab-pane active" id="my_profiles">
                <?php if (empty($myProfiles)): ?>
                    <div class="card-box text-center" style="padding:40px;">
                        <i class="fa fa-heart-o" style="font-size:48px; color:#cbd5e1; margin-bottom:15px;"></i>
                        <h4 style="color:#64748b; font-weight:600;">You haven't created any matrimonial profiles yet.</h4>
                        <p style="color:#94a3b8; font-size:13px; max-width:480px; margin:0 auto 20px;">
                            Create a matrimonial biodata for yourself, your son, daughter, brother, sister, or relative to unlock member candidate photographs and connect with prospective families.
                        </p>
                        <a href="matrimonial-create.php" class="btn btn-success" style="background:#009146; border:none; padding:10px 24px; font-weight:700;">
                            <i class="fa fa-plus"></i> Register Profile Now
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($myProfiles as $p): ?>
                            <div class="col-md-6">
                                <div class="card-box" style="border-left: 4px solid #009146;">
                                    <div style="display:flex; gap:16px; align-items:center;">
                                        <img src="<?php echo !empty($p['primary_photo']) && file_exists(__DIR__ . '/' . $p['primary_photo']) ? htmlspecialchars($p['primary_photo']) : 'images/dummy-avatar.svg'; ?>" class="profile-thumb" alt="Candidate">
                                        <div style="flex-grow:1;">
                                            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                                <div>
                                                    <span class="badge" style="background:#009146;"><?php echo htmlspecialchars($p['profile_code'] ?? 'ERQ'); ?></span>
                                                    <span style="font-size:12px; color:#64748b; font-weight:600; margin-left:4px;">For <?php echo ucfirst($p['profile_for']); ?></span>
                                                    <h4 style="margin:4px 0; font-weight:700; color:#1e293b;"><?php echo htmlspecialchars($p['full_name']); ?></h4>
                                                </div>
                                                <div>
                                                    <?php if ($p['status'] === 'active'): ?>
                                                        <span class="badge-status badge-active">Active</span>
                                                    <?php elseif ($p['status'] === 'married'): ?>
                                                        <span class="badge-status badge-married">💍 Married</span>
                                                    <?php elseif ($p['status'] === 'pending_approval'): ?>
                                                        <span class="badge-status badge-pending">⏳ Under Review</span>
                                                    <?php else: ?>
                                                        <span class="badge-status badge-pending"><?php echo ucfirst($p['status']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div style="font-size:12px; color:#64748b; margin-top:4px;">
                                                <span><?php echo $p['age']; ?> Yrs, <?php echo htmlspecialchars($p['height']); ?></span> • 
                                                <span><?php echo htmlspecialchars($p['qualification']); ?></span> • 
                                                <span><?php echo htmlspecialchars($p['present_city']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="margin-top:16px; padding-top:12px; border-top:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                                        <div style="display:flex; gap:6px; align-items:center;">
                                            <a href="matrimonial-profile-view.php?id=<?php echo $p['id']; ?>" class="btn btn-default btn-action-sm">
                                                <i class="fa fa-eye"></i> View Card
                                            </a>
                                            <a href="matrimonial-edit.php?id=<?php echo $p['id']; ?>" class="btn btn-warning btn-action-sm" style="background:#f59e0b; color:#fff; border:none;">
                                                <i class="fa fa-pencil"></i> Edit Biodata
                                            </a>
                                        </div>
                                        <form method="POST" action="matrimonial-manage.php" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle_profile_status">
                                            <input type="hidden" name="profile_id" value="<?php echo $p['id']; ?>">
                                            <?php if ($p['status'] === 'active'): ?>
                                                <input type="hidden" name="new_status" value="married">
                                                <button type="submit" class="btn btn-info btn-action-sm" onclick="return confirm('Mark this candidate profile as Married / Settled?');">
                                                    💍 Mark as Married
                                                </button>
                                            <?php elseif ($p['status'] === 'married'): ?>
                                                <input type="hidden" name="new_status" value="active">
                                                <button type="submit" class="btn btn-success btn-action-sm">
                                                    <i class="fa fa-check"></i> Re-activate
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TAB 2: Received Interests -->
            <div role="tabpanel" class="tab-pane" id="received_interests">
                <?php if (empty($receivedInterests)): ?>
                    <div class="card-box text-center" style="padding:40px;">
                        <i class="fa fa-inbox" style="font-size:48px; color:#cbd5e1; margin-bottom:12px;"></i>
                        <h4 style="color:#64748b; font-weight:600;">No Interests Received Yet</h4>
                        <p style="color:#94a3b8; font-size:13px;">When other Eraquee families show interest in your candidate profile, their proposals will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($receivedInterests as $item): ?>
                            <div class="col-md-6">
                                <div class="card-box" style="border-left: 4px solid <?php echo $item['status'] === 'accepted' ? '#16a34a' : ($item['status'] === 'declined' ? '#dc2626' : '#d97706'); ?>;">
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                        <div>
                                            <span class="badge" style="background:#009146;"><?php echo htmlspecialchars($item['sender_code']); ?></span>
                                            <span style="font-size:12px; color:#64748b; margin-left:4px;">Interested in: <strong><?php echo htmlspecialchars($item['my_candidate_name']); ?></strong></span>
                                            <h4 style="margin:4px 0; font-weight:700; color:#1e293b;"><?php echo htmlspecialchars($item['sender_name']); ?></h4>
                                            <div style="font-size:12px; color:#64748b;">
                                                <?php echo $item['sender_age']; ?> Yrs • <?php echo htmlspecialchars($item['sender_qual']); ?> • <?php echo htmlspecialchars($item['sender_occup']); ?> • <?php echo htmlspecialchars($item['sender_city']); ?>
                                            </div>
                                        </div>
                                        <div>
                                            <?php if ($item['status'] === 'accepted'): ?>
                                                <span class="badge-status badge-active">Mutual Interest</span>
                                            <?php elseif ($item['status'] === 'declined'): ?>
                                                <span class="badge-status badge-declined">Declined</span>
                                            <?php else: ?>
                                                <span class="badge-status badge-pending">Pending Response</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if (!empty($item['message'])): ?>
                                        <div style="background:#f8fafc; padding:8px 12px; border-radius:6px; font-size:12px; margin-top:10px; color:#475569;">
                                            <em>"<?php echo htmlspecialchars($item['message']); ?>"</em>
                                        </div>
                                    <?php endif; ?>

                                    <div style="margin-top:15px; padding-top:12px; border-top:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
                                        <a href="matrimonial-profile-view.php?id=<?php echo $item['sender_profile_id']; ?>" class="btn btn-default btn-action-sm">
                                            <i class="fa fa-user"></i> View Profile
                                        </a>
                                        <?php if ($item['status'] === 'pending'): ?>
                                            <div style="display:flex; gap:6px;">
                                                <form method="POST" action="matrimonial-manage.php" style="display:inline;">
                                                    <input type="hidden" name="action" value="accept_interest">
                                                    <input type="hidden" name="interest_id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" class="btn btn-success btn-action-sm">
                                                        <i class="fa fa-check"></i> Accept Interest
                                                    </button>
                                                </form>
                                                <form method="POST" action="matrimonial-manage.php" style="display:inline;">
                                                    <input type="hidden" name="action" value="decline_interest">
                                                    <input type="hidden" name="interest_id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-action-sm">
                                                        <i class="fa fa-times"></i> Decline
                                                    </button>
                                                </form>
                                            </div>
                                        <?php elseif ($item['status'] === 'accepted'): ?>
                                            <form method="POST" action="matrimonial-manage.php" style="display:inline;">
                                                <input type="hidden" name="action" value="request_admin_contact">
                                                <input type="hidden" name="target_profile_id" value="<?php echo $item['sender_profile_id']; ?>">
                                                <input type="hidden" name="my_profile_id" value="<?php echo $item['receiver_profile_id']; ?>">
                                                <button type="submit" class="btn btn-primary btn-action-sm" style="background:#0284c7; border:none;">
                                                    <i class="fa fa-phone"></i> Request Full Contact via Admin
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TAB 3: Sent Interests -->
            <div role="tabpanel" class="tab-pane" id="sent_interests">
                <?php if (empty($sentInterests)): ?>
                    <div class="card-box text-center" style="padding:40px;">
                        <i class="fa fa-paper-plane-o" style="font-size:48px; color:#cbd5e1; margin-bottom:12px;"></i>
                        <h4 style="color:#64748b; font-weight:600;">No Interests Sent Yet</h4>
                        <p style="color:#94a3b8; font-size:13px;">Browse candidate profiles in the matrimonial directory and click "Show Interest" to propose an alliance.</p>
                        <a href="matrimonial.php" class="btn btn-success" style="background:#009146; border:none; padding:8px 20px; font-weight:700;">
                            Browse Directory
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($sentInterests as $s): ?>
                            <div class="col-md-6">
                                <div class="card-box">
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                        <div>
                                            <span class="badge" style="background:#009146;"><?php echo htmlspecialchars($s['target_code']); ?></span>
                                            <span style="font-size:12px; color:#64748b; margin-left:4px;">Sent on behalf of: <strong><?php echo htmlspecialchars($s['my_candidate_name']); ?></strong></span>
                                            <div style="font-size:13px; font-weight:700; color:#1e293b; margin-top:4px;">
                                                Candidate: <?php echo $s['target_age']; ?> Yrs, <?php echo htmlspecialchars($s['target_qual']); ?> (<?php echo htmlspecialchars($s['target_city']); ?>)
                                            </div>
                                        </div>
                                        <div>
                                            <?php if ($s['status'] === 'accepted'): ?>
                                                <span class="badge-status badge-active">🎉 Accepted</span>
                                            <?php elseif ($s['status'] === 'declined'): ?>
                                                <span class="badge-status badge-declined">Declined</span>
                                            <?php else: ?>
                                                <span class="badge-status badge-pending">⏳ Pending</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div style="margin-top:15px; padding-top:12px; border-top:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
                                        <a href="matrimonial-profile-view.php?id=<?php echo $s['target_id']; ?>" class="btn btn-default btn-action-sm">
                                            <i class="fa fa-user"></i> View Profile
                                        </a>
                                        <?php if ($s['status'] === 'accepted'): ?>
                                            <form method="POST" action="matrimonial-manage.php" style="display:inline;">
                                                <input type="hidden" name="action" value="request_admin_contact">
                                                <input type="hidden" name="target_profile_id" value="<?php echo $s['target_id']; ?>">
                                                <input type="hidden" name="my_profile_id" value="<?php echo $s['sender_profile_id']; ?>">
                                                <button type="submit" class="btn btn-primary btn-action-sm" style="background:#0284c7; border:none;">
                                                    <i class="fa fa-phone"></i> Request Full Contact via Admin
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TAB 4: Approved Contact Unlocks -->
            <div role="tabpanel" class="tab-pane" id="approved_contacts">
                <?php if (empty($approvedContacts)): ?>
                    <div class="card-box text-center" style="padding:40px;">
                        <i class="fa fa-lock" style="font-size:48px; color:#cbd5e1; margin-bottom:12px;"></i>
                        <h4 style="color:#64748b; font-weight:600;">No Contact Details Unlocked Yet</h4>
                        <p style="color:#94a3b8; font-size:13px; max-width:480px; margin:0 auto;">
                            When you establish mutual interest with an Eraquee family and the Admin approves contact exchange, their direct phone numbers, WhatsApp, and residential addresses will appear here.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($approvedContacts as $c): ?>
                            <div class="col-md-6">
                                <div class="card-box" style="border: 2px solid #86efac; background:#f0fdf4;">
                                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #dcfce7; padding-bottom:10px; margin-bottom:12px;">
                                        <div>
                                            <span class="badge" style="background:#009146;"><?php echo htmlspecialchars($c['profile_code']); ?></span>
                                            <strong style="color:#166534; font-size:16px; margin-left:6px;"><?php echo htmlspecialchars($c['full_name']); ?></strong>
                                        </div>
                                        <span class="badge-status badge-active"><i class="fa fa-check-circle"></i> Verified & Approved</span>
                                    </div>

                                    <div style="font-size:13px; color:#1e293b;">
                                        <div style="margin-bottom:6px;">
                                            <strong>👤 Guardian / Contact Person:</strong> <?php echo htmlspecialchars($c['contact_person_name']); ?> (<?php echo htmlspecialchars($c['contact_relation']); ?>)
                                        </div>
                                        <div style="margin-bottom:6px;">
                                            <strong>📞 Phone Number:</strong> <a href="tel:<?php echo htmlspecialchars($c['contact_phone']); ?>" style="color:#009146; font-weight:700;"><?php echo htmlspecialchars($c['contact_phone']); ?></a>
                                        </div>
                                        <?php if (!empty($c['contact_whatsapp'])): ?>
                                            <div style="margin-bottom:6px;">
                                                <strong>💬 WhatsApp:</strong> <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $c['contact_whatsapp']); ?>" target="_blank" style="color:#16a34a; font-weight:700;"><?php echo htmlspecialchars($c['contact_whatsapp']); ?></a>
                                            </div>
                                        <?php endif; ?>
                                        <div style="margin-bottom:6px;">
                                            <strong>📍 Residential Address:</strong> <?php echo htmlspecialchars($c['full_address'] ?? ($c['present_city'] . ', ' . $c['present_state'])); ?>
                                        </div>
                                    </div>

                                    <div style="margin-top:15px; text-align:right;">
                                        <a href="matrimonial-profile-view.php?id=<?php echo $c['id']; ?>" class="btn btn-default btn-action-sm">
                                            <i class="fa fa-file-text-o"></i> View Full Biodata
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>

