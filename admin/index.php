<?php
require_once __DIR__ . '/auth.php';
check_admin_auth();

// Load settings
$settingsFile = __DIR__ . '/../data/settings.json';
$settings = [
    "topbar_phone_1" => "+91 9006297386",
    "topbar_phone_2" => "+91 9472502044",
    "topbar_phone_3" => "+91 9738455404",
    "contact_email" => "info@anjumaneraquee.org",
    "convenor_name" => "Abul Farah Sb.",
    "convenor_phone" => "+91 9006297386",
    "whatsapp_number" => "+91 9006297386",
    "office_address" => "Anjuman Eraquee INDIA",
    "youtube_video_url" => "https://www.youtube.com/watch?v=sn4zqErnfvc"
];

if (file_exists($settingsFile)) {
    $loaded = json_decode(file_get_contents($settingsFile), true);
    if (is_array($loaded)) {
        $settings = array_merge($settings, $loaded);
    }
}

// Check database connection & count members
$totalMembers = 0;
$totalResetRequests = 0;
$dbConnected = false;
$recentMembers = [];

require_once __DIR__ . '/../db.php';
$conn = get_db_connection();
if ($conn) {
    $dbConnected = true;
    $res = @mysqli_query($conn, "SELECT COUNT(*) as cnt, SUM(CASE WHEN reset_requested = 1 THEN 1 ELSE 0 END) as reset_cnt FROM user_registrtion");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        $totalMembers = intval($row['cnt']);
        $totalResetRequests = intval($row['reset_cnt']);
    }
    $recentRes = @mysqli_query($conn, "SELECT username, email, phonenumber, presentdistrict, created_at FROM user_registrtion ORDER BY id DESC LIMIT 5");
    if ($recentRes) {
        while ($m = mysqli_fetch_assoc($recentRes)) {
            $recentMembers[] = $m;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Anjuman Eraquee Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="icon" href="../images/icon/tabicon.jpeg" type="image/gif">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <h2>Anjuman <span>Eraquee</span></h2>
            </div>
            <ul class="admin-nav">
                <li class="active"><a href="index.php">📊 Dashboard</a></li>
                <li><a href="settings.php">⚙️ Site Settings & Phones</a></li>
                <li><a href="members.php">👥 Registered Members</a></li>
                <li><a href="../index.html" target="_blank">🌐 View Live Website</a></li>
            </ul>
            <div class="admin-nav-footer">
                <a href="logout.php" class="btn-logout">🚪 Log Out</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-topbar">
                <h1>Dashboard Overview</h1>
                <div class="admin-user-info">
                    <span class="badge-user">Logged in as: Super Admin</span>
                    <a href="logout.php" style="color: #ef4444; text-decoration: none; font-size: 14px; font-weight: 600;">Logout</a>
                </div>
            </header>

            <div class="admin-content">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="label">Total Registered Members</div>
                        <div class="value" style="color: #009146;"><?php echo number_format($totalMembers); ?></div>
                        <div style="font-size: 13px; color: #64748b; margin-top: 4px;">
                            <a href="members.php" style="color: #009146; text-decoration: none; font-weight: 600;">View Directory &rarr;</a>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="label">Password Reset Requests</div>
                        <div class="value" style="color: <?php echo $totalResetRequests > 0 ? '#b45309' : '#009146'; ?>;"><?php echo number_format($totalResetRequests); ?></div>
                        <div style="font-size: 13px; color: #64748b; margin-top: 4px;">
                            <?php if ($totalResetRequests > 0): ?>
                                <a href="members.php?filter=reset_requested" style="color: #b45309; font-weight: 700;">Action required &rarr;</a>
                            <?php else: ?>
                                <span style="color: #10b981;">✓ No pending requests</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="label">Convenor</div>
                        <div class="value" style="font-size: 20px;"><?php echo htmlspecialchars($settings['convenor_name']); ?></div>
                        <div style="font-size: 13px; color: #64748b; margin-top: 4px;"><?php echo htmlspecialchars($settings['convenor_phone']); ?></div>
                    </div>
                </div>

                <!-- Active Topbar Phone Numbers Card -->
                <div class="card">
                    <div class="card-header">
                        <h3>📞 Active Topbar Phone Numbers</h3>
                        <a href="settings.php" class="btn-primary" style="padding: 6px 14px; font-size: 13px;">Edit Numbers</a>
                    </div>
                    <div class="card-body">
                        <p style="color: #64748b; margin-bottom: 16px; font-size: 14px;">
                            These 3 numbers are actively displayed in the top bar across the website (class <code>list-inline count-list</code>):
                        </p>
                        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                            <div style="background: #f8fafc; padding: 12px 20px; border-radius: 8px; border: 1px solid #e2e8f0; flex: 1; min-width: 200px;">
                                <strong style="display: block; color: #64748b; font-size: 12px;">PHONE NUMBER 1</strong>
                                <span style="font-size: 16px; font-weight: 700; color: #009146;"><?php echo htmlspecialchars($settings['topbar_phone_1']); ?></span>
                            </div>
                            <div style="background: #f8fafc; padding: 12px 20px; border-radius: 8px; border: 1px solid #e2e8f0; flex: 1; min-width: 200px;">
                                <strong style="display: block; color: #64748b; font-size: 12px;">PHONE NUMBER 2</strong>
                                <span style="font-size: 16px; font-weight: 700; color: #009146;"><?php echo htmlspecialchars($settings['topbar_phone_2']); ?></span>
                            </div>
                            <div style="background: #f8fafc; padding: 12px 20px; border-radius: 8px; border: 1px solid #e2e8f0; flex: 1; min-width: 200px;">
                                <strong style="display: block; color: #64748b; font-size: 12px;">PHONE NUMBER 3</strong>
                                <span style="font-size: 16px; font-weight: 700; color: #009146;"><?php echo htmlspecialchars($settings['topbar_phone_3']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Registrations Card -->
                <div class="card">
                    <div class="card-header">
                        <h3>👥 Recent Registered Members</h3>
                        <a href="members.php" class="btn-secondary" style="padding: 6px 14px; font-size: 13px;">View All Members</a>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <?php if (empty($recentMembers)): ?>
                            <div style="padding: 24px; text-align: center; color: #64748b;">
                                No recent member registrations found in database.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>District</th>
                                            <th>Registered Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentMembers as $m): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($m['username'] ?? 'N/A'); ?></strong></td>
                                                <td><?php echo htmlspecialchars($m['email'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($m['phonenumber'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($m['presentdistrict'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($m['created_at'] ?? 'N/A'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

