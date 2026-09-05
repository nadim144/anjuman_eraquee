<?php
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: user-login.php');
    exit;
}

require_once __DIR__ . '/db.php';
$conn = get_db_connection();

if (!$conn) {
    die("Database connection error.");
}

$user = null;
if (isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    $res = mysqli_query($conn, "SELECT * FROM user_registrtion WHERE id = $uid LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $user = mysqli_fetch_assoc($res);
    }
} else if (isset($_SESSION['user_phone'])) {
    $phone = mysqli_real_escape_string($conn, $_SESSION['user_phone']);
    $res = mysqli_query($conn, "SELECT * FROM user_registrtion WHERE phonenumber = '$phone' LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $user = mysqli_fetch_assoc($res);
    }
}

if (!$user) {
    die("Member record not found.");
}

require_once __DIR__ . '/fpdf/fpdf.php';

class MembershipCertificate extends FPDF {
    function drawBorders() {
        // Outer decorative green border
        $this->SetDrawColor(0, 145, 70); // #009146
        $this->SetLineWidth(1.5);
        $this->Rect(8, 8, 194, 281);

        // Inner thin amber border
        $this->SetDrawColor(217, 119, 6); // #d97706
        $this->SetLineWidth(0.6);
        $this->Rect(11, 11, 188, 275);

        // Corner decorative marks
        $this->SetDrawColor(0, 145, 70);
        $this->SetLineWidth(0.4);
        $this->Line(13, 13, 23, 13);
        $this->Line(13, 13, 13, 23);

        $this->Line(197, 13, 187, 13);
        $this->Line(197, 13, 197, 23);

        $this->Line(13, 284, 23, 284);
        $this->Line(13, 284, 13, 274);

        $this->Line(197, 284, 187, 284);
        $this->Line(197, 284, 197, 274);
    }
}

$pdf = new MembershipCertificate('P', 'mm', 'A4');
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();
$pdf->drawBorders();

// Logo
$logoPath = __DIR__ . '/images/logo/logo.png';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 92, 16, 26);
    $pdf->SetY(44);
} else {
    $pdf->SetY(20);
}

// Title Header
$pdf->SetFont('Arial', 'B', 22);
$pdf->SetTextColor(0, 145, 70);
$pdf->Cell(0, 9, 'ANJUMAN ERAQUEE INDIA', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(100, 116, 139);
$pdf->Cell(0, 6, 'OFFICIAL MEMBERSHIP CERTIFICATE', 0, 1, 'C');

$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(140, 140, 140);
$pdf->Cell(0, 5, 'Registered Community Organization | Empowering Brotherhood & Progress', 0, 1, 'C');

// Horizontal divider
$pdf->SetY($pdf->GetY() + 3);
$pdf->SetDrawColor(217, 119, 6);
$pdf->SetLineWidth(0.8);
$pdf->Line(35, $pdf->GetY(), 175, $pdf->GetY());
$pdf->Ln(5);

// Certificate Intro Text
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(70, 70, 70);
$pdf->Cell(0, 6, 'This is to certify that', 0, 1, 'C');

// Member Full Name
$fullName = strtoupper(utf8_decode($user['username'] ?? 'MEMBER'));
$pdf->SetFont('Arial', 'B', 17);
$pdf->SetTextColor(0, 100, 50);
$pdf->Cell(0, 9, $fullName, 0, 1, 'C');

// Subtext
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(80, 80, 80);
$regId = 'AEI-' . str_pad($user['id'] ?? 1, 5, '0', STR_PAD_LEFT);
$pdf->Cell(0, 5, 'is an officially enrolled member of Anjuman Eraquee INDIA under Registration No: ' . $regId, 0, 1, 'C');
$pdf->Ln(5);

// Details Table Box
$startX = 18;
$startY = $pdf->GetY();
$tableW = 174;

// Fill background for header row
$pdf->SetFillColor(240, 248, 243);
$pdf->SetDrawColor(180, 215, 195);
$pdf->SetLineWidth(0.3);

$details = [
    ['Member ID', $regId, 'Registered Date', !empty($user['created_at']) ? substr($user['created_at'], 0, 10) : date('Y-m-d')],
    ['Full Name', utf8_decode($user['username'] ?? '-'), 'Father\'s Name', utf8_decode($user['fathername'] ?? '-')],
    ['Mother\'s Name', utf8_decode($user['mothername'] ?? '-'), 'Grandfather\'s Name', utf8_decode($user['grandfathername'] ?? '-')],
    ['Date of Birth', !empty($user['dob']) ? $user['dob'] : '-', 'Age / Gender', ($user['age'] ?? '-') . ' yrs / ' . ($user['gender'] ?? '-')],
    ['Marital Status', utf8_decode($user['maritalstatus'] ?? '-'), 'Native Place', utf8_decode($user['nativeplace'] ?? '-')],
    ['Phone Number', $user['phonenumber'] ?? '-', 'WhatsApp Number', $user['whatsappnumber'] ?? '-'],
    ['Email Address', utf8_decode($user['email'] ?? '-'), 'District / State', utf8_decode(($user['presentdistrict'] ?? '-') . ', ' . ($user['presentstate'] ?? '-'))],
    ['Qualification', utf8_decode($user['qulification'] ?? '-'), 'Occupation', utf8_decode($user['occupation'] ?? '-')],
];

foreach ($details as $row) {
    $pdf->SetX($startX);
    // Col 1 label
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(0, 100, 50);
    $pdf->SetFillColor(245, 250, 247);
    $pdf->Cell(35, 6.5, '  ' . $row[0], 1, 0, 'L', true);

    // Col 1 value
    $pdf->SetFont('Arial', '', 8.5);
    $pdf->SetTextColor(30, 30, 30);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(52, 6.5, '  ' . substr($row[1], 0, 28), 1, 0, 'L', false);

    // Col 2 label
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(0, 100, 50);
    $pdf->SetFillColor(245, 250, 247);
    $pdf->Cell(35, 6.5, '  ' . $row[2], 1, 0, 'L', true);

    // Col 2 value
    $pdf->SetFont('Arial', '', 8.5);
    $pdf->SetTextColor(30, 30, 30);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(52, 6.5, '  ' . substr($row[3], 0, 28), 1, 1, 'L', false);
}

$pdf->Ln(4);

// Welcoming Paragraph
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(60, 60, 60);
$welcomeMsg = "On behalf of Anjuman Eraquee INDIA, we warmly welcome you to our community fold. May our collective endeavors bring unity, educational empowerment, and socio-economic advancement for all members.";
$pdf->SetX($startX);
$pdf->MultiCell($tableW, 4.5, $welcomeMsg, 0, 'C');

$pdf->Ln(6);

// Signatures & Verification Seal
$sigY = $pdf->GetY() + 5;

// Digital Seal representation
$pdf->SetDrawColor(0, 145, 70);
$pdf->SetLineWidth(0.6);
$pdf->Rect(22, $sigY - 2, 45, 20);
$pdf->SetXY(22, $sigY);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(0, 145, 70);
$pdf->Cell(45, 4, 'ANJUMAN ERAQUEE', 0, 1, 'C');
$pdf->SetX(22);
$pdf->SetFont('Arial', '', 7);
$pdf->Cell(45, 3.5, 'OFFICIALLY VERIFIED', 0, 1, 'C');
$pdf->SetX(22);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(45, 3.5, date('d M Y'), 0, 1, 'C');

// Authority Signature
$pdf->SetXY(135, $sigY);
$pdf->SetFont('Arial', 'B', 9.5);
$pdf->SetTextColor(0, 100, 50);
$pdf->Cell(55, 4, 'Abul Farah Sb.', 0, 1, 'C');
$pdf->SetX(135);
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(90, 90, 90);
$pdf->Cell(55, 4, 'Convenor, Anjuman Eraquee INDIA', 0, 1, 'C');
$pdf->SetX(135);
$pdf->SetFont('Arial', 'I', 7.5);
$pdf->Cell(55, 3.5, 'National Head Office', 0, 1, 'C');

// Bottom Security & Contact Line
$pdf->SetY(272);
$pdf->SetFont('Arial', '', 7.5);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(0, 4, 'Helpline: +91 9006297386 | Official Portal: www.anjumaneraquee.org | Registered Member Certificate', 0, 1, 'C');

// Output PDF for download
$filename = 'Anjuman_Eraquee_Certificate_' . $regId . '.pdf';
$pdf->Output('D', $filename);
exit;