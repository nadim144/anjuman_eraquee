<?php
require_once __DIR__ . '/auth.php';
check_admin_auth();

require_once __DIR__ . '/../db.php';
$conn = get_db_connection();

$feedbackMsg = '';
$feedbackType = 'success';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    $action = $_POST['action'] ?? '';

    // DELETE MEMBER
    if ($action === 'delete_member') {
        $deleteId = intval($_POST['member_id'] ?? 0);
        if ($deleteId > 0) {
            $delRes = mysqli_query($conn, "DELETE FROM user_registrtion WHERE id = $deleteId");
            if ($delRes) {
                $feedbackMsg = "Member #$deleteId has been permanently deleted.";
                $feedbackType = 'success';
            } else {
                $feedbackMsg = "Error deleting member: " . mysqli_error($conn);
                $feedbackType = 'danger';
            }
        }
    }

    // UPDATE MEMBER
    else if ($action === 'update_member') {
        $editId = intval($_POST['member_id'] ?? 0);
        if ($editId > 0) {
            $u_name = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
            $u_father = mysqli_real_escape_string($conn, $_POST['fathername'] ?? '');
            $u_mother = mysqli_real_escape_string($conn, $_POST['mothername'] ?? '');
            $u_dob = mysqli_real_escape_string($conn, $_POST['dob'] ?? '');
            $u_age = mysqli_real_escape_string($conn, $_POST['age'] ?? '');

            if (!empty($u_dob)) {
                try {
                    $dobD = new DateTime($u_dob);
                    $u_age = (string)$dobD->diff(new DateTime('today'))->y;
                } catch (Exception $e) {}
            }

            $u_gender = mysqli_real_escape_string($conn, $_POST['gender'] ?? '');
            $u_marital = mysqli_real_escape_string($conn, $_POST['maritalstatus'] ?? '');
            $u_phone = mysqli_real_escape_string($conn, $_POST['phonenumber'] ?? '');
            $u_whatsapp = mysqli_real_escape_string($conn, $_POST['whatsappnumber'] ?? '');
            $u_email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
            $u_native = mysqli_real_escape_string($conn, $_POST['nativeplace'] ?? '');
            $u_district = mysqli_real_escape_string($conn, $_POST['presentdistrict'] ?? '');
            $u_state = mysqli_real_escape_string($conn, $_POST['presentstate'] ?? '');
            $u_qual = mysqli_real_escape_string($conn, $_POST['qulification'] ?? '');
            $u_occup = mysqli_real_escape_string($conn, $_POST['occupation'] ?? '');

            $updSql = "UPDATE user_registrtion SET 
                username = '$u_name',
                fathername = '$u_father',
                mothername = '$u_mother',
                dob = " . (!empty($u_dob) ? "'$u_dob'" : "NULL") . ",
                age = '$u_age',
                gender = '$u_gender',
                maritalstatus = '$u_marital',
                phonenumber = '$u_phone',
                whatsappnumber = '$u_whatsapp',
                email = '$u_email',
                nativeplace = '$u_native',
                presentdistrict = '$u_district',
                presentstate = '$u_state',
                qulification = '$u_qual',
                occupation = '$u_occup'
                WHERE id = $editId";

            if (mysqli_query($conn, $updSql)) {
                $feedbackMsg = "Member #$editId details updated successfully!";
                $feedbackType = 'success';
            } else {
                $feedbackMsg = "Failed to update member: " . mysqli_error($conn);
                $feedbackType = 'danger';
            }
        }
    }

    // ISSUE TEMPORARY PASSWORD
    else if ($action === 'issue_temp_pwd') {
        $pwdId = intval($_POST['member_id'] ?? 0);
        $tempPassword = trim($_POST['temp_password'] ?? '');
        if (empty($tempPassword)) {
            $tempPassword = 'Temp@' . rand(1000, 9999);
        }

        if ($pwdId > 0) {
            $hashed = password_hash($tempPassword, PASSWORD_BCRYPT);
            $escaped = mysqli_real_escape_string($conn, $hashed);

            $res = mysqli_query($conn, "UPDATE user_registrtion SET password = '$escaped', is_temp_password = 1, reset_requested = 0 WHERE id = $pwdId");
            if ($res) {
                $feedbackMsg = "Temporary password generated for Member #$pwdId: <strong style='color:#009146; font-size:16px;'>$tempPassword</strong>. Please provide this to the member. They will be required to set their permanent password upon login.";
                $feedbackType = 'success';
            } else {
                $feedbackMsg = "Failed to set temporary password: " . mysqli_error($conn);
                $feedbackType = 'danger';
            }
        }
    }
}

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv' && $conn) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=anjuman_members_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Father Name', 'Mother Name', 'DOB', 'Age', 'Native Place', 'Gender', 'Marital Status', 'Email', 'Phone', 'WhatsApp', 'District', 'State', 'Qualification', 'Occupation', 'Registered Date']);
    
    $res = mysqli_query($conn, "SELECT * FROM user_registrtion ORDER BY id DESC");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            fputcsv($output, [
                $row['id'] ?? '',
                $row['username'] ?? '',
                $row['fathername'] ?? '',
                $row['mothername'] ?? '',
                $row['dob'] ?? '',
                $row['age'] ?? '',
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
$filterReset = isset($_GET['filter']) && $_GET['filter'] === 'reset_requested';
$members = [];
$totalCount = 0;
$resetCount = 0;

if ($conn) {
    $cRes = mysqli_query($conn, "SELECT COUNT(*) as total, SUM(CASE WHEN reset_requested = 1 THEN 1 ELSE 0 END) as reset_total FROM user_registrtion");
    if ($cRes && $row = mysqli_fetch_assoc($cRes)) {
        $totalCount = intval($row['total']);
        $resetCount = intval($row['reset_total']);
    }

    $where = [];
    if (!empty($search)) {
        $escaped = mysqli_real_escape_string($conn, $search);
        $where[] = "(username LIKE '%$escaped%' OR email LIKE '%$escaped%' OR phonenumber LIKE '%$escaped%' OR presentdistrict LIKE '%$escaped%' OR nativeplace LIKE '%$escaped%')";
    }
    if ($filterReset) {
        $where[] = "reset_requested = 1";
    }

    $whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";
    $sql = "SELECT * FROM user_registrtion $whereClause ORDER BY id DESC";
    $res = mysqli_query($conn, $sql);
    if ($res) {
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
    <title>Member Management | Anjuman Eraquee Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="icon" href="../images/icon/tabicon.jpeg" type="image/gif">
    <style>
        .search-bar-container {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        .search-input {
            flex-grow: 1;
            max-width: 400px;
            min-width: 240px;
        }
        .btn-action {
            padding: 5px 9px;
            font-size: 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-weight: 600;
        }
        .btn-action-view { background: #e0f2fe; color: #0369a1; }
        .btn-action-view:hover { background: #bae6fd; }
        .btn-action-edit { background: #fef3c7; color: #b45309; }
        .btn-action-edit:hover { background: #fde68a; }
        .btn-action-key { background: #dcfce7; color: #15803d; }
        .btn-action-key:hover { background: #bbf7d0; }
        .btn-action-delete { background: #fee2e2; color: #b91c1c; }
        .btn-action-delete:hover { background: #fecaca; }

        .badge-pill {
            display: inline-block;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 9999px;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(2px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-content-box {
            background: #fff;
            width: 100%;
            max-width: 650px;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 8px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3);
            border-top: 4px solid #009146;
            padding: 24px;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .modal-header h3 {
            margin: 0;
            font-size: 18px;
            color: #009146;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #64748b;
        }
        .modal-close:hover { color: #000; }
        .modal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 12px;
        }
        .detail-item {
            background: #f8fafc;
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .detail-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
        }
        .detail-value {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            margin-top: 2px;
            word-break: break-word;
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
                <?php if ($feedbackMsg): ?>
                    <div class="alert alert-<?php echo $feedbackType; ?>">
                        <?php echo $feedbackMsg; ?>
                    </div>
                <?php endif; ?>

                <?php if (!$conn): ?>
                    <div class="alert alert-danger">
                        ⚠️ Database is currently not reachable.
                    </div>
                <?php endif; ?>

                <!-- Filter & Stat Badges -->
                <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
                    <a href="members.php" class="btn-secondary" style="padding: 8px 14px; font-size: 13px; <?php echo !$filterReset ? 'background:#009146; color:#fff;' : ''; ?>">
                        👥 All Members (<?php echo $totalCount; ?>)
                    </a>
                    <a href="members.php?filter=reset_requested" class="btn-secondary" style="padding: 8px 14px; font-size: 13px; <?php echo $filterReset ? 'background:#b45309; color:#fff;' : ''; ?>">
                        🔑 Password Reset Requests (<?php echo $resetCount; ?>)
                    </a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Community Members (<?php echo count($members); ?> shown)</h3>
                        <?php if ($conn && $totalCount > 0): ?>
                            <a href="members.php?export=csv" class="btn-secondary" style="padding: 6px 14px; font-size: 13px;">📥 Export to CSV</a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <!-- Search Form -->
                        <form method="GET" action="members.php" class="search-bar-container">
                            <?php if ($filterReset): ?>
                                <input type="hidden" name="filter" value="reset_requested">
                            <?php endif; ?>
                            <input type="text" name="q" class="form-control search-input" placeholder="Search by name, email, phone, district..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn-primary" style="padding: 10px 18px; font-size: 14px;">🔍 Search</button>
                            <?php if (!empty($search) || $filterReset): ?>
                                <a href="members.php" class="btn-secondary" style="padding: 10px 14px; font-size: 14px;">Reset Filter</a>
                            <?php endif; ?>
                        </form>

                        <!-- Members Table -->
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Full Name</th>
                                        <th>Contact (Phone/Email)</th>
                                        <th>DOB / Age</th>
                                        <th>Native / District</th>
                                        <th>Status</th>
                                        <th style="min-width: 220px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($members)): ?>
                                        <tr>
                                            <td colspan="7" style="text-align: center; padding: 30px; color: #64748b;">
                                                <?php echo !empty($search) ? 'No members found matching your search query.' : 'No registered members recorded yet.'; ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($members as $index => $m): ?>
                                            <tr>
                                                <td><?php echo $m['id']; ?></td>
                                                <td>
                                                    <strong style="color: #009146;"><?php echo htmlspecialchars($m['username'] ?? ''); ?></strong>
                                                    <div style="font-size: 11px; color: #64748b;">Father: <?php echo htmlspecialchars($m['fathername'] ?? '-'); ?></div>
                                                </td>
                                                <td>
                                                    <div>📞 <?php echo htmlspecialchars($m['phonenumber'] ?? '-'); ?></div>
                                                    <div style="font-size: 12px; color: #64748b;">✉️ <?php echo htmlspecialchars($m['email'] ?? '-'); ?></div>
                                                </td>
                                                <td>
                                                    <div><?php echo !empty($m['dob']) ? htmlspecialchars($m['dob']) : '-'; ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($m['age'] ?? '-'); ?> yrs</small>
                                                </td>
                                                <td>
                                                    <div><?php echo htmlspecialchars($m['nativeplace'] ?? '-'); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($m['presentdistrict'] ?? '-'); ?></small>
                                                </td>
                                                <td>
                                                    <?php if (!empty($m['reset_requested']) && intval($m['reset_requested']) === 1): ?>
                                                        <span class="badge-pill badge-warning">⚠️ Reset Requested</span>
                                                    <?php elseif (!empty($m['is_temp_password']) && intval($m['is_temp_password']) === 1): ?>
                                                        <span class="badge-pill badge-warning">Temp Pwd Active</span>
                                                    <?php else: ?>
                                                        <span class="badge-pill badge-success">Active</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                                        <button type="button" class="btn-action btn-action-view" onclick='viewMember(<?php echo json_encode($m, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
                                                            👁️ Details
                                                        </button>
                                                        <button type="button" class="btn-action btn-action-edit" onclick='editMember(<?php echo json_encode($m, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
                                                            ✏️ Edit
                                                        </button>
                                                        <button type="button" class="btn-action btn-action-key" onclick='openTempPwdModal(<?php echo $m['id']; ?>, "<?php echo htmlspecialchars(addslashes($m['username'] ?? '')); ?>")'>
                                                            🔑 Temp Pwd
                                                        </button>
                                                        <form method="POST" action="members.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to permanently delete member #<?php echo $m['id']; ?> (<?php echo htmlspecialchars(addslashes($m['username'] ?? '')); ?>)? This action cannot be undone.');">
                                                            <input type="hidden" name="action" value="delete_member">
                                                            <input type="hidden" name="member_id" value="<?php echo $m['id']; ?>">
                                                            <button type="submit" class="btn-action btn-action-delete">
                                                                🗑️ Delete
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

    <!-- MODAL: View Member Details -->
    <div id="viewModal" class="modal-overlay">
        <div class="modal-content-box">
            <div class="modal-header">
                <h3><i class="fa fa-user"></i> Full Member Details</h3>
                <button type="button" class="modal-close" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div class="modal-grid" id="viewModalDetails">
                <!-- Injected via JavaScript -->
            </div>
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" class="btn-secondary" onclick="closeModal('viewModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- MODAL: Edit Member Details -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-content-box">
            <div class="modal-header">
                <h3><i class="fa fa-pencil"></i> Edit Member Information</h3>
                <button type="button" class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="POST" action="members.php">
                <input type="hidden" name="action" value="update_member">
                <input type="hidden" name="member_id" id="edit_member_id">

                <div class="modal-grid">
                    <div>
                        <label style="font-size:12px; font-weight:700;">Full Name *</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:700;">Father's Name</label>
                        <input type="text" name="fathername" id="edit_fathername" class="form-control">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:700;">Mother's Name</label>
                        <input type="text" name="mothername" id="edit_mothername" class="form-control">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:700;">Date of Birth</label>
                        <input type="date" name="dob" id="edit_dob" class="form-control">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:700;">Age</label>
                        <input type="text" name="age" id="edit_age" class="form-control">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:700;">Gender</label>
                        <select name="gender" id="edit_gender" class="form-control">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:700;">Marital Status</label>
                        <select name="maritalstatus" id="edit_maritalstatus" class="form-control">
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Divorced">Divorced</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:700;">Phone Number</label>
                        <input type="text" name="phonenumber" id="edit_phonenumber" class="form-control" required>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:700;">WhatsApp Number</label>
                        <input type="text" name="whatsappnumber" id="edit_whatsappnumber" class="form-control">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:700;">Email Address</label>
                        <input type="email" name="email" id="edit_email" class="form-control">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:700;">Native Place</label>
                        <input type="text" name="nativeplace" id="edit_nativeplace" class="form-control">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:700;">District</label>
                        <input type="text" name="presentdistrict" id="edit_presentdistrict" class="form-control">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:700;">State</label>
                        <input type="text" name="presentstate" id="edit_presentstate" class="form-control">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:700;">Qualification</label>
                        <input type="text" name="qulification" id="edit_qulification" class="form-control">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:700;">Occupation</label>
                        <input type="text" name="occupation" id="edit_occupation" class="form-control">
                    </div>
                </div>

                <div style="text-align: right; margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="btn-primary" style="padding: 8px 20px;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Issue Temporary Password -->
    <div id="tempPwdModal" class="modal-overlay">
        <div class="modal-content-box" style="max-width: 450px;">
            <div class="modal-header">
                <h3><i class="fa fa-key"></i> Issue Temporary Password</h3>
                <button type="button" class="modal-close" onclick="closeModal('tempPwdModal')">&times;</button>
            </div>
            <form method="POST" action="members.php">
                <input type="hidden" name="action" value="issue_temp_pwd">
                <input type="hidden" name="member_id" id="pwd_member_id">

                <p style="font-size: 13px; color: #475569; margin-bottom: 15px;">
                    Setting a temporary password for <strong id="pwd_member_name"></strong>. Upon next login, the user will be forced to change this to their personal permanent password.
                </p>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-size: 12px; font-weight: 700;">Temporary Password</label>
                    <input type="text" name="temp_password" id="temp_pwd_val" class="form-control" placeholder="Leave empty for auto-generated password">
                    <small style="color: #64748b; font-size: 11px;">Example: Temp@4821</small>
                </div>

                <div style="text-align: right; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn-secondary" onclick="closeModal('tempPwdModal')">Cancel</button>
                    <button type="submit" class="btn-primary" style="padding: 8px 18px;">Set & Issue Password</button>
                </div>
            </form>
        </div>
    </div>

<script>
function viewMember(m) {
    var detailsHtml = '';
    var fields = [
        ['Registration ID', '#' + (m.id || '')],
        ['Full Name', m.username || '-'],
        ['Father\'s Name', m.fathername || '-'],
        ['Mother\'s Name', m.mothername || '-'],
        ['Grandfather\'s Name', m.grandfathername || '-'],
        ['Date of Birth', m.dob || '-'],
        ['Age', (m.age ? m.age + ' yrs' : '-')],
        ['Gender', m.gender || '-'],
        ['Marital Status', m.maritalstatus || '-'],
        ['Phone Number', m.phonenumber || '-'],
        ['WhatsApp Number', m.whatsappnumber || '-'],
        ['Email Address', m.email || '-'],
        ['Native Place', m.nativeplace || '-'],
        ['Present District', m.presentdistrict || '-'],
        ['Present State', m.presentstate || '-'],
        ['Present Address', m.presentaddress || '-'],
        ['Permanent Address', m.permanentaddress || '-'],
        ['Qualification', m.qulification || '-'],
        ['Qualification Details', m.qualificationdetails || '-'],
        ['Occupation', m.occupation || '-'],
        ['Occupation Details', m.occupationdetails || '-'],
        ['Feedback / Message', m.messageinfo || '-'],
        ['Registered At', m.created_at || '-']
    ];

    fields.forEach(function(item) {
        detailsHtml += '<div class="detail-item"><div class="detail-label">' + item[0] + '</div><div class="detail-value">' + (item[1] || '-') + '</div></div>';
    });

    document.getElementById('viewModalDetails').innerHTML = detailsHtml;
    document.getElementById('viewModal').style.display = 'flex';
}

function editMember(m) {
    document.getElementById('edit_member_id').value = m.id || '';
    document.getElementById('edit_username').value = m.username || '';
    document.getElementById('edit_fathername').value = m.fathername || '';
    document.getElementById('edit_mothername').value = m.mothername || '';
    document.getElementById('edit_dob').value = m.dob || '';
    document.getElementById('edit_age').value = m.age || '';
    document.getElementById('edit_gender').value = m.gender || 'Male';
    document.getElementById('edit_maritalstatus').value = m.maritalstatus || 'Single';
    document.getElementById('edit_phonenumber').value = m.phonenumber || '';
    document.getElementById('edit_whatsappnumber').value = m.whatsappnumber || '';
    document.getElementById('edit_email').value = m.email || '';
    document.getElementById('edit_nativeplace').value = m.nativeplace || '';
    document.getElementById('edit_presentdistrict').value = m.presentdistrict || '';
    document.getElementById('edit_presentstate').value = m.presentstate || '';
    document.getElementById('edit_qulification').value = m.qulification || '';
    document.getElementById('edit_occupation').value = m.occupation || '';

    document.getElementById('editModal').style.display = 'flex';
}

function openTempPwdModal(id, name) {
    document.getElementById('pwd_member_id').value = id;
    document.getElementById('pwd_member_name').innerText = name + ' (#' + id + ')';
    var autoPass = 'Temp@' + Math.floor(1000 + Math.random() * 9000);
    document.getElementById('temp_pwd_val').value = autoPass;
    document.getElementById('tempPwdModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.style.display = 'none';
    }
}
</script>

</body>
</html>