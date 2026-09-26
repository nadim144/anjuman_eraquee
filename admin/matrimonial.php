<?php
require_once __DIR__ . '/auth.php';
check_admin_auth();

require_once __DIR__ . '/../db.php';
$conn = get_db_connection();

$feedbackMsg = '';
$feedbackType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    $action = $_POST['action'] ?? '';

    // APPROVE CONTACT UNLOCK REQUEST
    if ($action === 'approve_contact_request') {
        $requestId = intval($_POST['request_id'] ?? 0);
        $adminId = intval($_SESSION['admin_id'] ?? 1);
        $upd = mysqli_query($conn, "UPDATE matrimonial_access_requests SET status = 'approved_by_admin', reviewed_by_admin_id = $adminId, reviewed_at = NOW() WHERE id = $requestId");
        if ($upd) {
            $feedbackMsg = "Contact Access Request #$requestId approved! The requesting family can now view direct phone and address details.";
            $feedbackType = 'success';
        }
    }

    // REJECT CONTACT UNLOCK REQUEST
    else if ($action === 'reject_contact_request') {
        $requestId = intval($_POST['request_id'] ?? 0);
        $adminId = intval($_SESSION['admin_id'] ?? 1);
        $upd = mysqli_query($conn, "UPDATE matrimonial_access_requests SET status = 'rejected_by_admin', reviewed_by_admin_id = $adminId, reviewed_at = NOW() WHERE id = $requestId");
        if ($upd) {
            $feedbackMsg = "Contact Access Request #$requestId has been declined.";
            $feedbackType = 'warning';
        }
    }

    // APPROVE PROFILE BIODATA
    else if ($action === 'approve_profile') {
        $profId = intval($_POST['profile_id'] ?? 0);
        if ($profId > 0) {
            $upd = mysqli_query($conn, "UPDATE matrimonial_profiles SET status = 'active', updated_at = NOW() WHERE id = $profId");
            if ($upd) {
                $feedbackMsg = "Candidate profile #$profId approved and published to active directory!";
                $feedbackType = 'success';
            }
        }
    }

    // REJECT / HIDE PROFILE BIODATA
    else if ($action === 'reject_profile') {
        $profId = intval($_POST['profile_id'] ?? 0);
        if ($profId > 0) {
            $upd = mysqli_query($conn, "UPDATE matrimonial_profiles SET status = 'hidden', updated_at = NOW() WHERE id = $profId");
            if ($upd) {
                $feedbackMsg = "Candidate profile #$profId status updated to Hidden / Suspended.";
                $feedbackType = 'warning';
            }
        }
    }

    // TOGGLE PROFILE STATUS
    else if ($action === 'update_profile_status') {
        $profId = intval($_POST['profile_id'] ?? 0);
        $newStatus = mysqli_real_escape_string($conn, $_POST['new_status'] ?? 'active');
        if (in_array($newStatus, ['active', 'hidden', 'married', 'pending_approval'])) {
            mysqli_query($conn, "UPDATE matrimonial_profiles SET status = '$newStatus', updated_at = NOW() WHERE id = $profId");
            $feedbackMsg = "Profile #$profId status updated to " . ucfirst(str_replace('_', ' ', $newStatus)) . ".";
            $feedbackType = 'success';
        }
    }

    // DELETE PROFILE
    else if ($action === 'delete_profile') {
        $profId = intval($_POST['profile_id'] ?? 0);
        if ($profId > 0) {
            mysqli_query($conn, "DELETE FROM matrimonial_photos WHERE profile_id = $profId");
            mysqli_query($conn, "DELETE FROM matrimonial_interests WHERE sender_profile_id = $profId OR receiver_profile_id = $profId");
            mysqli_query($conn, "DELETE FROM matrimonial_access_requests WHERE target_profile_id = $profId");
            $del = mysqli_query($conn, "DELETE FROM matrimonial_profiles WHERE id = $profId");
            if ($del) {
                $feedbackMsg = "Matrimonial profile #$profId deleted successfully.";
                $feedbackType = 'success';
            }
        }
    }
}

// Fetch Profiles Awaiting Admin Approval
$pendingProfiles = [];
if ($conn) {
    $pAppSql = "SELECT p.*, u.username AS registered_by_name, u.phonenumber AS registered_by_phone, u.email AS registered_by_email
                FROM matrimonial_profiles p
                JOIN user_registrtion u ON p.created_by_user_id = u.id
                WHERE p.status = 'pending_approval'
                ORDER BY p.updated_at DESC, p.id DESC";
    $pAppRes = mysqli_query($conn, $pAppSql);
    if ($pAppRes) {
        while ($p = mysqli_fetch_assoc($pAppRes)) {
            $pendingProfiles[] = $p;
        }
    }
}

// Fetch Pending Contact Requests
$pendingRequests = [];
if ($conn) {
    $reqSql = "SELECT ar.*, 
                      u.username AS requester_member_name, u.phonenumber AS requester_phone, u.email AS requester_email,
                      tp.profile_code AS target_code, tp.full_name AS target_name, tp.gender AS target_gender,
                      tp.contact_person_name, tp.contact_phone,
                      sp.profile_code AS sender_candidate_code, sp.full_name AS sender_candidate_name
               FROM matrimonial_access_requests ar
               JOIN user_registrtion u ON ar.requester_user_id = u.id
               JOIN matrimonial_profiles tp ON ar.target_profile_id = tp.id
               LEFT JOIN matrimonial_profiles sp ON ar.requester_profile_id = sp.id
               ORDER BY CASE WHEN ar.status = 'pending' THEN 1 ELSE 2 END, ar.id DESC";
    $rRes = mysqli_query($conn, $reqSql);
    if ($rRes) {
        while ($r = mysqli_fetch_assoc($rRes)) {
            $pendingRequests[] = $r;
        }
    }
}

// Fetch All Profiles
$allProfiles = [];
if ($conn) {
    $pSql = "SELECT p.*, u.username AS registered_by_name, u.phonenumber AS registered_by_phone
             FROM matrimonial_profiles p
             JOIN user_registrtion u ON p.created_by_user_id = u.id
             ORDER BY p.id DESC";
    $pRes = mysqli_query($conn, $pSql);
    if ($pRes) {
        while ($p = mysqli_fetch_assoc($pRes)) {
            $allProfiles[] = $p;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matrimonial Moderation | Anjuman Eraquee Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="icon" href="../images/icon/tabicon.jpeg" type="image/gif">
    <style>
        .badge-status-pill {
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 9999px;
            display: inline-block;
        }
        .status-pending { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .status-approved { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .status-rejected { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .tab-btn {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            margin-right: 6px;
        }
        .tab-btn.active {
            background: #009146;
            color: #fff;
            border-color: #009146;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <h2>Anjuman <span>Eraquee</span></h2>
            </div>
            <ul class="admin-nav">
                <li><a href="index.php">📊 Dashboard</a></li>
                <li><a href="settings.php">⚙️ Site Settings & Phones</a></li>
                <li><a href="members.php">👥 Registered Members</a></li>
                <li class="active"><a href="matrimonial.php">💍 Matrimonial Management</a></li>
                <?php if (is_super_admin()): ?>
                    <li><a href="admins.php">🛡️ Manage Admins</a></li>
                <?php endif; ?>
                <li><a href="../matrimonial.php" target="_blank">🌐 View Matrimonial Directory</a></li>
            </ul>
            <div class="admin-nav-footer">
                <a href="logout.php" class="btn-logout">🚪 Log Out</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-topbar">
                <h1>Community Matrimonial Moderation</h1>
                <div class="admin-user-info">
                    <span class="badge-user"><?php echo is_super_admin() ? '👑 Super Admin' : '🛡️ Admin'; ?>: <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                    <a href="logout.php" style="color: #ef4444; text-decoration: none; font-size: 14px; font-weight: 600;">Logout</a>
                </div>
            </header>

            <div class="admin-content">
                <?php if (!empty($feedbackMsg)): ?>
                    <div class="alert alert-<?php echo $feedbackType; ?>" style="padding: 12px 18px; border-radius: 6px; margin-bottom: 20px;">
                        <?php echo htmlspecialchars($feedbackMsg); ?>
                    </div>
                <?php endif; ?>

                <!-- Stats Summary -->
                <div class="stats-grid" style="margin-bottom: 24px;">
                    <div class="stat-card">
                        <div class="label">Total Candidate Profiles</div>
                        <div class="value" style="font-size:26px; font-weight:700; color:var(--dark-bg); margin-top:6px;">
                            <?php echo count($allProfiles); ?>
                        </div>
                    </div>
                    <div class="stat-card" style="border-left: 4px solid #f59e0b;">
                        <div class="label">Profiles Awaiting Approval</div>
                        <div class="value" style="font-size:26px; font-weight:700; color:#d97706; margin-top:6px;">
                            <?php echo count($pendingProfiles); ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Contact Access Requests</div>
                        <div class="value" style="font-size:26px; font-weight:700; color:#0284c7; margin-top:6px;">
                            <?php echo count($pendingRequests); ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Contact Requests Pending</div>
                        <div class="value" style="font-size:26px; font-weight:700; color:#dc2626; margin-top:6px;">
                            <?php 
                                $pendCount = 0;
                                foreach ($pendingRequests as $pr) {
                                    if ($pr['status'] === 'pending') $pendCount++;
                                }
                                echo $pendCount;
                            ?>
                        </div>
                    </div>
                </div>

                <?php 
                    $defaultTab = (count($pendingProfiles) > 0) ? 'approvals' : 'requests';
                ?>

                <!-- Tabs -->
                <div style="margin-bottom: 20px; display:flex; gap:8px; flex-wrap:wrap;">
                    <button type="button" class="tab-btn <?php echo ($defaultTab === 'approvals') ? 'active' : ''; ?>" id="btnTabApprovals" onclick="showTab('approvals')">
                        ⏳ Profile Approvals (<?php echo count($pendingProfiles); ?>)
                    </button>
                    <button type="button" class="tab-btn <?php echo ($defaultTab === 'requests') ? 'active' : ''; ?>" id="btnTabRequests" onclick="showTab('requests')">
                        📩 Contact Access Requests (<?php echo $pendCount; ?> Pending)
                    </button>
                    <button type="button" class="tab-btn" id="btnTabProfiles" onclick="showTab('profiles')">
                        👥 All Candidate Profiles (<?php echo count($allProfiles); ?>)
                    </button>
                </div>

                <!-- SECTION 1: Pending Profile Approvals -->
                <div id="sectionApprovals" class="card" style="<?php echo ($defaultTab === 'approvals') ? '' : 'display:none;'; ?>">
                    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <h3>Candidate Profiles Awaiting Approval</h3>
                            <small style="color:#64748b;">Review newly created or modified candidate biodatas before they are published to the public directory.</small>
                        </div>
                        <span class="badge" style="background:#f59e0b; font-size:13px; padding:6px 12px; font-weight:700;">
                            <?php echo count($pendingProfiles); ?> Awaiting Review
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Candidate Name</th>
                                        <th>Gender / For</th>
                                        <th>Age / Location</th>
                                        <th>Education / Profession</th>
                                        <th>Registered Member</th>
                                        <th>Last Modified</th>
                                        <th>Adjudication Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($pendingProfiles)): ?>
                                        <tr>
                                            <td colspan="8" style="text-align:center; padding:35px; color:#64748b;">
                                                <i class="fa fa-check-circle" style="font-size:24px; color:#22c55e;"></i><br>
                                                All candidate profiles are approved! No profiles awaiting verification.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($pendingProfiles as $appP): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge" style="background:#009146; font-weight:700;">
                                                        <?php echo htmlspecialchars($appP['profile_code'] ?? 'ERQ-CANDIDATE'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($appP['full_name']); ?></strong>
                                                    <div style="font-size:11px; color:#64748b;">
                                                        Marital: <?php echo ucfirst(str_replace('_', ' ', $appP['marital_status'])); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong><?php echo ($appP['gender'] === 'male') ? 'Dulha (Male)' : 'Dulhan (Female)'; ?></strong>
                                                    <div style="font-size:11px; color:#64748b;">For: <?php echo ucfirst($appP['profile_for']); ?></div>
                                                </td>
                                                <td>
                                                    <div><?php echo $appP['age']; ?> Yrs, <?php echo htmlspecialchars($appP['height']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($appP['present_city'] . ', ' . $appP['present_state']); ?></small>
                                                </td>
                                                <td>
                                                    <div><?php echo htmlspecialchars($appP['occupation']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($appP['qualification']); ?></small>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($appP['registered_by_name']); ?></strong>
                                                    <div style="font-size:11px; color:#64748b;">📞 <?php echo htmlspecialchars($appP['registered_by_phone']); ?></div>
                                                </td>
                                                <td>
                                                    <?php echo !empty($appP['updated_at']) ? date('d M Y, h:i A', strtotime($appP['updated_at'])) : date('d M Y, h:i A', strtotime($appP['created_at'])); ?>
                                                </td>
                                                <td>
                                                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                                        <a href="../matrimonial-profile-view.php?id=<?php echo $appP['id']; ?>" target="_blank" class="btn btn-default" style="padding:4px 8px; font-size:11px; border:1px solid #cbd5e1; border-radius:4px; font-weight:600;">
                                                            👁️ View
                                                        </a>
                                                        <a href="../matrimonial-edit.php?id=<?php echo $appP['id']; ?>" target="_blank" class="btn btn-warning" style="padding:4px 8px; font-size:11px; background:#f59e0b; color:#fff; border:none; border-radius:4px; font-weight:600;">
                                                            ✏️ Edit
                                                        </a>
                                                        <form method="POST" action="matrimonial.php" style="display:inline;">
                                                            <input type="hidden" name="action" value="approve_profile">
                                                            <input type="hidden" name="profile_id" value="<?php echo $appP['id']; ?>">
                                                            <button type="submit" class="btn btn-success" style="background:#16a34a; color:#fff; border:none; padding:4px 10px; font-size:11px; border-radius:4px; font-weight:700;">
                                                                ✅ Approve
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="matrimonial.php" style="display:inline;" onsubmit="return confirm('Reject / suspend this candidate profile?');">
                                                            <input type="hidden" name="action" value="reject_profile">
                                                            <input type="hidden" name="profile_id" value="<?php echo $appP['id']; ?>">
                                                            <button type="submit" class="btn btn-danger" style="background:#fee2e2; color:#b91c1c; border:none; padding:4px 8px; font-size:11px; border-radius:4px; font-weight:600;">
                                                                Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: Contact Requests -->
                <div id="sectionRequests" class="card" style="<?php echo ($defaultTab === 'requests') ? '' : 'display:none;'; ?>">
                    <div class="card-header">
                        <h3>Family Contact Exchange Requests</h3>
                        <small style="color:#64748b;">Review and approve requests before family contact numbers and full addresses are released.</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Requester Family</th>
                                        <th>Target Candidate Profile</th>
                                        <th>Request Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($pendingRequests)): ?>
                                        <tr>
                                            <td colspan="6" style="text-align:center; padding:30px; color:#64748b;">
                                                No contact exchange requests submitted yet.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($pendingRequests as $req): ?>
                                            <tr>
                                                <td><?php echo $req['id']; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($req['requester_member_name']); ?></strong>
                                                    <div style="font-size:11px; color:#64748b;">
                                                        📞 <?php echo htmlspecialchars($req['requester_phone']); ?>
                                                    </div>
                                                    <?php if (!empty($req['sender_candidate_code'])): ?>
                                                        <div style="font-size:11px; color:#009146; font-weight:600;">
                                                            On behalf of: <?php echo htmlspecialchars($req['sender_candidate_code'] . ' - ' . $req['sender_candidate_name']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge" style="background:#009146;"><?php echo htmlspecialchars($req['target_code']); ?></span>
                                                    <strong><?php echo htmlspecialchars($req['target_name']); ?></strong> (<?php echo ucfirst($req['target_gender']); ?>)
                                                    <div style="font-size:11px; color:#64748b;">
                                                        Guardian: <?php echo htmlspecialchars($req['contact_person_name']); ?> (<?php echo htmlspecialchars($req['contact_phone']); ?>)
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php echo date('d M Y, h:i A', strtotime($req['requested_at'])); ?>
                                                </td>
                                                <td>
                                                    <?php if ($req['status'] === 'pending'): ?>
                                                        <span class="badge-status-pill status-pending">⏳ Pending Review</span>
                                                    <?php elseif ($req['status'] === 'approved_by_admin'): ?>
                                                        <span class="badge-status-pill status-approved">✅ Approved</span>
                                                    <?php else: ?>
                                                        <span class="badge-status-pill status-rejected">❌ Rejected</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($req['status'] === 'pending'): ?>
                                                        <div style="display:flex; gap:6px;">
                                                            <form method="POST" action="matrimonial.php" style="display:inline;">
                                                                <input type="hidden" name="action" value="approve_contact_request">
                                                                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                                                <button type="submit" class="btn btn-success" style="background:#16a34a; color:#fff; border:none; padding:4px 10px; font-size:12px; border-radius:4px; font-weight:600;">
                                                                    ✅ Approve Release
                                                                </button>
                                                            </form>
                                                            <form method="POST" action="matrimonial.php" style="display:inline;">
                                                                <input type="hidden" name="action" value="reject_contact_request">
                                                                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                                                <button type="submit" class="btn btn-danger" style="background:#fee2e2; color:#b91c1c; border:none; padding:4px 10px; font-size:12px; border-radius:4px; font-weight:600;">
                                                                    Decline
                                                                </button>
                                                            </form>
                                                        </div>
                                                    <?php else: ?>
                                                        <span style="font-size:12px; color:#64748b;">Reviewed</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: All Profiles -->
                <div id="sectionProfiles" class="card" style="display:none;">
                    <div class="card-header">
                        <h3>Registered Candidate Biodatas</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Candidate Name</th>
                                        <th>Gender / Status</th>
                                        <th>Age / City</th>
                                        <th>Education / Occupation</th>
                                        <th>Registered By Member</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($allProfiles)): ?>
                                        <tr>
                                            <td colspan="8" style="text-align:center; padding:30px; color:#64748b;">
                                                No candidate profiles recorded yet.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($allProfiles as $p): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge" style="background:#009146;"><?php echo htmlspecialchars($p['profile_code']); ?></span>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($p['full_name']); ?></strong>
                                                    <div style="font-size:11px; color:#64748b;">For: <?php echo ucfirst($p['profile_for']); ?></div>
                                                </td>
                                                <td>
                                                    <div><?php echo ($p['gender'] === 'male') ? 'Dulha (Groom)' : 'Dulhan (Bride)'; ?></div>
                                                    <small class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $p['marital_status'])); ?></small>
                                                </td>
                                                <td>
                                                    <div><?php echo $p['age']; ?> Yrs, <?php echo htmlspecialchars($p['height']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($p['present_city'] . ', ' . $p['present_state']); ?></small>
                                                </td>
                                                <td>
                                                    <div><?php echo htmlspecialchars($p['qualification']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($p['occupation']); ?></small>
                                                </td>
                                                <td>
                                                    <div><?php echo htmlspecialchars($p['registered_by_name']); ?></div>
                                                    <small class="text-muted">📞 <?php echo htmlspecialchars($p['registered_by_phone']); ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($p['status'] === 'active'): ?>
                                                        <span class="badge-status-pill status-approved">Active</span>
                                                    <?php elseif ($p['status'] === 'pending_approval'): ?>
                                                        <span class="badge-status-pill status-pending">⏳ Pending Approval</span>
                                                    <?php elseif ($p['status'] === 'married'): ?>
                                                        <span class="badge-status-pill status-pending">💍 Married</span>
                                                    <?php else: ?>
                                                        <span class="badge-status-pill status-rejected"><?php echo ucfirst($p['status']); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div style="display:flex; gap:4px; align-items:center;">
                                                        <a href="../matrimonial-profile-view.php?id=<?php echo $p['id']; ?>" target="_blank" class="btn btn-default" style="padding:4px 8px; font-size:11px; border:1px solid #cbd5e1; border-radius:4px; font-weight:600;">
                                                            👁️ View
                                                        </a>
                                                        <a href="../matrimonial-edit.php?id=<?php echo $p['id']; ?>" target="_blank" class="btn btn-warning" style="padding:4px 8px; font-size:11px; background:#f59e0b; color:#fff; border:none; border-radius:4px; font-weight:600;">
                                                            ✏️ Edit
                                                        </a>
                                                        <?php if ($p['status'] === 'pending_approval'): ?>
                                                            <form method="POST" action="matrimonial.php" style="display:inline;">
                                                                <input type="hidden" name="action" value="approve_profile">
                                                                <input type="hidden" name="profile_id" value="<?php echo $p['id']; ?>">
                                                                <button type="submit" class="btn btn-success" style="padding:4px 8px; font-size:11px; background:#16a34a; color:#fff; border:none; border-radius:4px; font-weight:600;">
                                                                    ✅ Approve
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <form method="POST" action="matrimonial.php" style="display:inline;" onsubmit="return confirm('Delete candidate profile #<?php echo $p['profile_code']; ?>? This cannot be undone.');">
                                                            <input type="hidden" name="action" value="delete_profile">
                                                            <input type="hidden" name="profile_id" value="<?php echo $p['id']; ?>">
                                                            <button type="submit" class="btn btn-danger" style="padding:4px 8px; font-size:11px; background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; border-radius:4px; font-weight:600;">
                                                                🗑️
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        function showTab(tabName) {
            var btnApprovals = document.getElementById('btnTabApprovals');
            var btnRequests = document.getElementById('btnTabRequests');
            var btnProfiles = document.getElementById('btnTabProfiles');
            var secApprovals = document.getElementById('sectionApprovals');
            var secRequests = document.getElementById('sectionRequests');
            var secProfiles = document.getElementById('sectionProfiles');

            btnApprovals.classList.remove('active');
            btnRequests.classList.remove('active');
            btnProfiles.classList.remove('active');
            secApprovals.style.display = 'none';
            secRequests.style.display = 'none';
            secProfiles.style.display = 'none';

            if (tabName === 'approvals') {
                btnApprovals.classList.add('active');
                secApprovals.style.display = 'block';
            } else if (tabName === 'requests') {
                btnRequests.classList.add('active');
                secRequests.style.display = 'block';
            } else {
                btnProfiles.classList.add('active');
                secProfiles.style.display = 'block';
            }
        }
    </script>
</body>
</html>

