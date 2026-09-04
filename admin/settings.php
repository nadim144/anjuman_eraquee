<?php
require_once __DIR__ . '/auth.php';
check_admin_auth();

$settingsFile = __DIR__ . '/../data/settings.json';
$successMsg = '';
$errorMsg = '';

// Default settings
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

// Load existing settings
if (file_exists($settingsFile)) {
    $loaded = json_decode(file_get_contents($settingsFile), true);
    if (is_array($loaded)) {
        $settings = array_merge($settings, $loaded);
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings['topbar_phone_1'] = trim($_POST['topbar_phone_1'] ?? '');
    $settings['topbar_phone_2'] = trim($_POST['topbar_phone_2'] ?? '');
    $settings['topbar_phone_3'] = trim($_POST['topbar_phone_3'] ?? '');
    $settings['contact_email'] = trim($_POST['contact_email'] ?? '');
    $settings['convenor_name'] = trim($_POST['convenor_name'] ?? '');
    $settings['convenor_phone'] = trim($_POST['convenor_phone'] ?? '');
    $settings['whatsapp_number'] = trim($_POST['whatsapp_number'] ?? '');
    $settings['office_address'] = trim($_POST['office_address'] ?? '');
    $settings['youtube_video_url'] = trim($_POST['youtube_video_url'] ?? '');

    // Ensure data directory exists
    $dataDir = dirname($settingsFile);
    if (!is_dir($dataDir)) {
        @mkdir($dataDir, 0755, true);
    }

    if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
        $successMsg = 'Settings saved successfully! All phone numbers and contact details have been updated on the website.';
    } else {
        $errorMsg = 'Could not write to data/settings.json. Please check folder permissions.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings & Phones | Anjuman Eraquee Admin</title>
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
                <li><a href="index.php">📊 Dashboard</a></li>
                <li class="active"><a href="settings.php">⚙️ Site Settings & Phones</a></li>
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
                <h1>Manage Site Content & Settings</h1>
                <div class="admin-user-info">
                    <span class="badge-user">Super Admin</span>
                    <a href="logout.php" style="color: #ef4444; text-decoration: none; font-size: 14px; font-weight: 600;">Logout</a>
                </div>
            </header>

            <div class="admin-content">
                <?php if (!empty($successMsg)): ?>
                    <div class="alert alert-success">✅ <?php echo htmlspecialchars($successMsg); ?></div>
                <?php endif; ?>

                <?php if (!empty($errorMsg)): ?>
                    <div class="alert alert-danger">❌ <?php echo htmlspecialchars($errorMsg); ?></div>
                <?php endif; ?>

                <form method="POST" action="settings.php">
                    <!-- Topbar Phone Numbers Section -->
                    <div class="card">
                        <div class="card-header">
                            <h3>📞 Topbar Header Phone Numbers</h3>
                        </div>
                        <div class="card-body">
                            <p style="color: #64748b; font-size: 14px; margin-bottom: 20px;">
                                These 3 numbers appear in the header top bar (class <code>list-inline count-list</code>) across all website pages.
                            </p>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="topbar_phone_1">Header Phone 1</label>
                                    <input type="text" id="topbar_phone_1" name="topbar_phone_1" class="form-control" value="<?php echo htmlspecialchars($settings['topbar_phone_1']); ?>" required placeholder="+91 9006297386">
                                    <span class="help-text">First telephone number in the header</span>
                                </div>

                                <div class="form-group">
                                    <label for="topbar_phone_2">Header Phone 2</label>
                                    <input type="text" id="topbar_phone_2" name="topbar_phone_2" class="form-control" value="<?php echo htmlspecialchars($settings['topbar_phone_2']); ?>" required placeholder="+91 9472502044">
                                    <span class="help-text">Second telephone number in the header</span>
                                </div>

                                <div class="form-group">
                                    <label for="topbar_phone_3">Header Phone 3</label>
                                    <input type="text" id="topbar_phone_3" name="topbar_phone_3" class="form-control" value="<?php echo htmlspecialchars($settings['topbar_phone_3']); ?>" required placeholder="+91 9738455404">
                                    <span class="help-text">Third telephone number in the header</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact & Convenor Information Section -->
                    <div class="card">
                        <div class="card-header">
                            <h3>🏢 Contact & Convenor Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="convenor_name">Convenor Name</label>
                                    <input type="text" id="convenor_name" name="convenor_name" class="form-control" value="<?php echo htmlspecialchars($settings['convenor_name']); ?>" placeholder="Abul Farah Sb.">
                                </div>

                                <div class="form-group">
                                    <label for="convenor_phone">Convenor Phone / Banner Phone</label>
                                    <input type="text" id="convenor_phone" name="convenor_phone" class="form-control" value="<?php echo htmlspecialchars($settings['convenor_phone']); ?>" placeholder="+91 9006297386">
                                </div>

                                <div class="form-group">
                                    <label for="whatsapp_number">WhatsApp Number</label>
                                    <input type="text" id="whatsapp_number" name="whatsapp_number" class="form-control" value="<?php echo htmlspecialchars($settings['whatsapp_number']); ?>" placeholder="+91 9006297386">
                                </div>

                                <div class="form-group">
                                    <label for="contact_email">Support / Contact Email</label>
                                    <input type="email" id="contact_email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($settings['contact_email']); ?>" placeholder="info@anjumaneraquee.org">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="office_address">Office / Organization Address</label>
                                <input type="text" id="office_address" name="office_address" class="form-control" value="<?php echo htmlspecialchars($settings['office_address']); ?>" placeholder="Anjuman Eraquee INDIA">
                            </div>

                            <div class="form-group">
                                <label for="youtube_video_url">YouTube Video URL</label>
                                <input type="text" id="youtube_video_url" name="youtube_video_url" class="form-control" value="<?php echo htmlspecialchars($settings['youtube_video_url']); ?>" placeholder="https://www.youtube.com/watch?v=...">
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 24px; display: flex; gap: 12px; align-items: center;">
                        <button type="submit" class="btn-primary" style="padding: 12px 36px; font-size: 16px;">💾 Save & Update Site</button>
                        <a href="index.php" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

