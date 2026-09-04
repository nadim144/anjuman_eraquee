<?php
require_once __DIR__ . '/auth.php';
check_admin_auth();

require_once __DIR__ . '/../db.php';
$conn = get_db_connection();

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv' && $conn) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=anjuman_members_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    
    // Header row
    fputcsv($output, ['ID', 'Name', 'Father Name', 'Mother Name', 'Native Place', 'Gender', 'Marital Status', 'Email', 'Phone', 'WhatsApp', 'District', 'State', 'Qualification', 'Occupation', 'Registered Date']);
    
    $res = mysqli_query($conn, "SELECT * FROM user_registrtion ORDER BY id DESC");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            fputcsv($output, [
                $row['id'] ?? '',
                $row['username'] ?? '',
                $row['fathername'] ?? '',
                $row['mothername'] ?? '',
                $row['nativeplace'] ?? '',
                $row['gender'] ?? '',
                $row['maritalstatus'] ?? '',
                $row['email'] ?? '',
                $row['phonenumber'] ?? '',
                $row['whatsappnumber'] ?? '',
                $row['presentdistrict'] ?? '',
                $row['presentstate'] ?? '',
                $row['qulification'] ?? '',
                $row['occupation'] ?? '',
                $row['created_at'] ?? ''
            ]);
        }
    }
    fclose($output);
    exit;
}

$search = trim($_GET['q'] ?? '');
$members = [];
$totalCount = 0;

if ($conn) {
    if (!empty($search)) {
        $escaped = mysqli_real_escape_string($conn, $search);
        $sql = "SELECT * FROM user_registrtion WHERE username LIKE '%$escaped%' OR email LIKE '%$escaped%' OR phonenumber LIKE '%$escaped%' OR presentdistrict LIKE '%$escaped%' OR nativeplace LIKE '%$escaped%' ORDER BY id DESC";
    } else {
        $sql = "SELECT * FROM user_registrtion ORDER BY id DESC";
    }
    
    $res = mysqli_query($conn, $sql);
    if ($res) {
        $totalCount = mysqli_num_rows($res);
        while ($row = mysqli_fetch_assoc($res)) {
            $members[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Members | Anjuman Eraquee Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="icon" href="../images/icon/tabicon.jpeg" type="image/gif">
    <style>
        .search-bar-container {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            align-items: center;
        }
        .search-input {
            flex-grow: 1;
            max-width: 400px;
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
                <li class="active"><a href="members.php">👥 Registered Members</a></li>
                <li><a href="../index.html" target="_blank">🌐 View Live Website</a></li>
            </ul>
            <div class="admin-nav-footer">
                <a href="logout.php" class="btn-logout">🚪 Log Out</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-topbar">
                <h1>Registered Members Directory</h1>
                <div class="admin-user-info">
                    <span class="badge-user">Super Admin</span>
                    <a href="logout.php" style="color: #ef4444; text-decoration: none; font-size: 14px; font-weight: 600;">Logout</a>
                </div>
            </header>

            <div class="admin-content">
                <?php if (!$conn): ?>
                    <div class="alert alert-danger">
                        ⚠️ Database is currently not reachable with default credentials. Once connected to MySQL, registered members will appear here automatically.
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3>Registered Community Members (<?php echo $totalCount; ?>)</h3>
                        <?php if ($conn && $totalCount > 0): ?>
                            <a href="members.php?export=csv" class="btn-secondary" style="padding: 6px 14px; font-size: 13px;">📥 Export to CSV</a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <!-- Search Form -->
                        <form method="GET" action="members.php" class="search-bar-container">
                            <input type="text" name="q" class="form-control search-input" placeholder="Search by name, email, phone, district..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn-primary" style="padding: 10px 18px; font-size: 14px;">🔍 Search</button>
                            <?php if (!empty($search)): ?>
                                <a href="members.php" class="btn-secondary" style="padding: 10px 14px; font-size: 14px;">Reset</a>
                            <?php endif; ?>
                        </form>

                        <!-- Members Table -->
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Full Name</th>
                                        <th>Father's Name</th>
                                        <th>Native Place</th>
                                        <th>Contact Phone</th>
                                        <th>Email</th>
                                        <th>District / State</th>
                                        <th>Occupation</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($members)): ?>
                                        <tr>
                                            <td colspan="9" style="text-align: center; padding: 30px; color: #64748b;">
                                                <?php echo !empty($search) ? 'No members found matching your search query.' : 'No registered members recorded yet.'; ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($members as $index => $m): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><strong style="color: #009146;"><?php echo htmlspecialchars($m['username'] ?? ''); ?></strong></td>
                                                <td><?php echo htmlspecialchars($m['fathername'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($m['nativeplace'] ?? '-'); ?></td>
                                                <td>
                                                    <a href="tel:<?php echo htmlspecialchars($m['phonenumber'] ?? ''); ?>" style="color: #334155; text-decoration: none;">
                                                        📞 <?php echo htmlspecialchars($m['phonenumber'] ?? '-'); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($m['email'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars(($m['presentdistrict'] ?? '') . ' / ' . ($m['presentstate'] ?? '')); ?></td>
                                                <td><?php echo htmlspecialchars($m['occupation'] ?? '-'); ?></td>
                                                <td style="font-size: 12px; color: #64748b;"><?php echo htmlspecialchars($m['created_at'] ?? '-'); ?></td>
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
</body>
</html>

