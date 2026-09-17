<?php
require_once __DIR__ . '/auth.php';
require_super_admin(); // Restrict to Super Admin only

require_once __DIR__ . '/../db.php';
$conn = get_db_connection();

$feedbackMsg = '';
$feedbackType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    $action = $_POST['action'] ?? '';

    if ($action === 'promote_member') {
        $memberId = intval($_POST['member_id'] ?? 0);
        if ($memberId <= 0) {
            $feedbackMsg = 'Please select a valid registered member to promote.';
            $feedbackType = 'danger';
        } else {
            // Verify member exists
            $uRes = mysqli_query($conn, "SELECT id, username, email, phonenumber FROM user_registrtion WHERE id = $memberId LIMIT 1");
            if ($uRes && $u = mysqli_fetch_assoc($uRes)) {
                // Check if already an admin
                $aCheck = mysqli_query($conn, "SELECT id, role, status FROM admin_users WHERE user_id = $memberId LIMIT 1");
                if ($aCheck && $existingAdmin = mysqli_fetch_assoc($aCheck)) {
                    if ($existingAdmin['status'] === 'inactive') {
                        mysqli_query($conn, "UPDATE admin_users SET status = 'active' WHERE id = " . intval($existingAdmin['id']));
                        $feedbackMsg = "Re-activated administrator access for <strong>" . htmlspecialchars($u['username']) . "</strong>.";
                        $feedbackType = 'success';
                    } else {
                        $feedbackMsg = "<strong>" . htmlspecialchars($u['username']) . "</strong> is already an active administrator.";
                        $feedbackType = 'warning';
                    }
                } else {
                    $creatorId = intval($_SESSION['admin_id'] ?? 1);
                    $insSql = "INSERT INTO admin_users (user_id, role, status, created_by) VALUES ($memberId, 'admin', 'active', $creatorId)";
                    if (mysqli_query($conn, $insSql)) {
                        $feedbackMsg = "Successfully promoted <strong>" . htmlspecialchars($u['username']) . "</strong> to Administrator! They can now log in using their member credentials.";
                        $feedbackType = 'success';
                    } else {
                        $feedbackMsg = "Failed to promote member: " . mysqli_error($conn);
                        $feedbackType = 'danger';
                    }
                }
            } else {
                $feedbackMsg = 'Member record not found.';
                $feedbackType = 'danger';
            }
        }
    } else if ($action === 'revoke_admin') {
        $adminId = intval($_POST['admin_id'] ?? 0);
        $aRes = mysqli_query($conn, "SELECT a.id, a.user_id, a.role, u.username, u.email 
                                     FROM admin_users a 
                                     JOIN user_registrtion u ON a.user_id = u.id 
                                     WHERE a.id = $adminId LIMIT 1");
        if ($aRes && $target = mysqli_fetch_assoc($aRes)) {
            // Safety: Never allow revoking Super Admin
            if ($target['role'] === 'super_admin' || strtolower($target['email']) === 'ahmad.nadim144@gmail.com') {
                $feedbackMsg = 'Action Denied: The primary Super Admin cannot be revoked or demoted.';
                $feedbackType = 'danger';
            } else {
                mysqli_query($conn, "DELETE FROM admin_users WHERE id = $adminId");
                $feedbackMsg = "Administrator privileges have been revoked from <strong>" . htmlspecialchars($target['username']) . "</strong>. Their regular member account remains active.";
                $feedbackType = 'success';
            }
        } else {
            $feedbackMsg = 'Administrator record not found.';
            $feedbackType = 'danger';
        }
    } else if ($action === 'toggle_status') {
        $adminId = intval($_POST['admin_id'] ?? 0);
        $aRes = mysqli_query($conn, "SELECT a.id, a.role, a.status, u.username, u.email 
                                     FROM admin_users a 
                                     JOIN user_registrtion u ON a.user_id = u.id 
                                     WHERE a.id = $adminId LIMIT 1");
        if ($aRes && $target = mysqli_fetch_assoc($aRes)) {
            if ($target['role'] === 'super_admin' || strtolower($target['email']) === 'ahmad.nadim144@gmail.com') {
                $feedbackMsg = 'Action Denied: The primary Super Admin account cannot be suspended.';
                $feedbackType = 'danger';
            } else {
                $newStatus = ($target['status'] === 'active') ? 'inactive' : 'active';
                mysqli_query($conn, "UPDATE admin_users SET status = '$newStatus' WHERE id = $adminId");
                $feedbackMsg = "Status for <strong>" . htmlspecialchars($target['username']) . "</strong> updated to <strong>" . ucfirst($newStatus) . "</strong>.";
                $feedbackType = 'success';
            }
        }
    }
}

// Fetch list of current admins
$admins = [];
if ($conn) {
    $adminListSql = "SELECT a.id AS admin_id, a.user_id, a.role, a.status, a.created_at, a.last_login,
                            u.username, u.email, u.phonenumber, u.profile_picture, u.presentdistrict
                     FROM admin_users a
                     JOIN user_registrtion u ON a.user_id = u.id
                     ORDER BY CASE WHEN a.role = 'super_admin' THEN 1 ELSE 2 END, a.id ASC";
    $aListRes = mysqli_query($conn, $adminListSql);
    if ($aListRes) {
        while ($row = mysqli_fetch_assoc($aListRes)) {
            $admins[] = $row;
        }
    }
}

// Fetch list of eligible members for promotion (non-admins)
$eligibleMembers = [];
if ($conn) {
    $eRes = mysqli_query($conn, "SELECT u.id, u.username, u.email, u.phonenumber 
                                 FROM user_registrtion u 
                                 LEFT JOIN admin_users a ON u.id = a.user_id 
                                 WHERE a.id IS NULL 
                                 ORDER BY u.username ASC");
    if ($eRes) {
        while ($row = mysqli_fetch_assoc($eRes)) {
            $eligibleMembers[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Administrators | Anjuman Eraquee Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="icon" href="../images/icon/tabicon.jpeg" type="image/gif">
    <style>
        .role-badge-super {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .role-badge-admin {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .status-badge-active {
            background-color: #dcfce7;
            color: #15803d;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-badge-inactive {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .promote-card {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            padding: 20px 24px;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .promote-form {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 12px;
        }
        .promote-select {
            flex-grow: 1;
            min-width: 280px;
            max-width: 500px;
            padding: 9px 12px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            font-size: 14px;
        }
        .btn-promote {
            background-color: var(--primary-color);
            color: #ffffff;
            border: none;
            padding: 9px 20px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-promote:hover {
            background-color: var(--primary-dark);
        }
        .btn-action-revoke {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-action-revoke:hover {
            background-color: #ef4444;
            color: #ffffff;
        }
        .btn-action-toggle {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-action-toggle:hover {
            background-color: #e2e8f0;
            color: #0f172a;
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
                <li class="active"><a href="admins.php">🛡️ Manage Admins</a></li>
                <li><a href="../index.html" target="_blank">🌐 View Live Website</a></li>
            </ul>
            <div class="admin-nav-footer">
                <a href="logout.php" class="btn-logout">🚪 Log Out</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-topbar">
                <h1>Administrator Management</h1>
                <div class="admin-user-info">
                    <span class="badge-user">👑 Super Admin: <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Super Admin'); ?></span>
                    <a href="logout.php" style="color: #ef4444; text-decoration: none; font-size: 14px; font-weight: 600;">Logout</a>
                </div>
            </header>

            <div class="admin-content">
                <?php if (!empty($feedbackMsg)): ?>
                    <div class="alert alert-<?php echo $feedbackType; ?>" style="padding: 12px 18px; border-radius: 6px; margin-bottom: 24px; font-size: 14px; background: <?php echo $feedbackType === 'success' ? '#dcfce7' : ($feedbackType === 'warning' ? '#fef3c7' : '#fee2e2'); ?>; border: 1px solid <?php echo $feedbackType === 'success' ? '#86efac' : ($feedbackType === 'warning' ? '#fde68a' : '#fca5a5'); ?>; color: <?php echo $feedbackType === 'success' ? '#166534' : ($feedbackType === 'warning' ? '#92400e' : '#991b1b'); ?>;">
                        <?php echo $feedbackMsg; ?>
                    </div>
                <?php endif; ?>

                <!-- Stats Overview -->
                <div class="stats-grid" style="margin-bottom: 24px;">
                    <div class="stat-card">
                        <div class="label">Total Administrators</div>
                        <div class="value" style="font-size: 28px; font-weight: 700; color: var(--dark-bg); margin-top: 8px;">
                            <?php echo count($admins); ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Super Administrators</div>
                        <div class="value" style="font-size: 28px; font-weight: 700; color: #d97706; margin-top: 8px;">
                            <?php 
                            $superCount = 0;
                            foreach ($admins as $a) { if ($a['role'] === 'super_admin') $superCount++; }
                            echo $superCount;
                            ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Regular Administrators</div>
                        <div class="value" style="font-size: 28px; font-weight: 700; color: var(--primary-color); margin-top: 8px;">
                            <?php echo count($admins) - $superCount; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Promote Section -->
                <div class="promote-card">
                    <h3 style="font-size: 16px; font-weight: 700; color: var(--dark-bg); margin-bottom: 4px;">
                        ⭐ Promote Registered Member to Administrator
                    </h3>
                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">
                        Select an existing community member to grant them Administrator privileges. They will log in using their registered mobile number/email and member password.
                    </p>
                    <form method="POST" action="admins.php" class="promote-form">
                        <input type="hidden" name="action" value="promote_member">
                        <select name="member_id" class="promote-select" required>
                            <option value="">-- Select Member to Grant Admin Access --</option>
                            <?php foreach ($eligibleMembers as $em): ?>
                                <option value="<?php echo $em['id']; ?>">
                                    #<?php echo $em['id']; ?> - <?php echo htmlspecialchars($em['username']); ?> (<?php echo htmlspecialchars($em['phonenumber']); ?> / <?php echo htmlspecialchars($em['email'] ?: 'No email'); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn-promote">
                            <span>🛡️ Grant Admin Role</span>
                        </button>
                    </form>
                </div>

                <!-- Administrators Table Card -->
                <div class="table-card" style="background: #fff; border-radius: 10px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
                    <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                        <h2 style="font-size: 17px; font-weight: 700; color: var(--dark-bg); margin: 0;">Active Administrators & Permissions</h2>
                        <span style="font-size: 13px; color: var(--text-muted);"><?php echo count($admins); ?> appointed</span>
                    </div>

                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Administrator Details</th>
                                    <th>Contact</th>
                                    <th>Assigned Role</th>
                                    <th>Status</th>
                                    <th>Appointed Date</th>
                                    <th>Last Login</th>
                                    <th style="min-width: 170px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $index => $adm): ?>
                                    <tr>
                                        <td>#<?php echo $adm['admin_id']; ?></td>
                                        <td>
                                            <strong style="color: var(--dark-bg); font-size: 14px;">
                                                <?php echo htmlspecialchars($adm['username']); ?>
                                            </strong>
                                            <div style="font-size: 11px; color: var(--text-muted);">
                                                Member Record: #<?php echo $adm['user_id']; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div>📞 +91 <?php echo htmlspecialchars($adm['phonenumber'] ?? '-'); ?></div>
                                            <div style="font-size: 12px; color: var(--text-muted);">✉️ <?php echo htmlspecialchars($adm['email'] ?? '-'); ?></div>
                                        </td>
                                        <td>
                                            <?php if ($adm['role'] === 'super_admin'): ?>
                                                <span class="role-badge-super">👑 Super Admin</span>
                                            <?php else: ?>
                                                <span class="role-badge-admin">🛡️ Admin</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($adm['status'] === 'active'): ?>
                                                <span class="status-badge-active">Active</span>
                                            <?php else: ?>
                                                <span class="status-badge-inactive">Suspended</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="font-size: 13px;">
                                                <?php echo !empty($adm['created_at']) ? substr($adm['created_at'], 0, 10) : '-'; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-size: 12px; color: var(--text-muted);">
                                                <?php echo !empty($adm['last_login']) ? htmlspecialchars($adm['last_login']) : 'Never'; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($adm['role'] === 'super_admin' || strtolower($adm['email']) === 'ahmad.nadim144@gmail.com'): ?>
                                                <span style="font-size: 12px; color: #94a3b8; font-weight: 600;">🔒 Primary Super Admin</span>
                                            <?php else: ?>
                                                <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                                    <form method="POST" action="admins.php" style="display: inline;">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="admin_id" value="<?php echo $adm['admin_id']; ?>">
                                                        <button type="submit" class="btn-action-toggle" title="Toggle active/suspended status">
                                                            <?php echo $adm['status'] === 'active' ? '⏸️ Suspend' : '▶️ Activate'; ?>
                                                        </button>
                                                    </form>

                                                    <form method="POST" action="admins.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to revoke Administrator access from <?php echo htmlspecialchars(addslashes($adm['username'])); ?>? They will return to being a regular member.');">
                                                        <input type="hidden" name="action" value="revoke_admin">
                                                        <input type="hidden" name="admin_id" value="<?php echo $adm['admin_id']; ?>">
                                                        <button type="submit" class="btn-action-revoke" title="Revoke Administrator power">
                                                            🚫 Revoke
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

