<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to save your details.', 'not_logged_in' => true]);
    exit;
}

require_once __DIR__ . '/../db.php';
$conn = get_db_connection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed. Please try again.']);
    exit;
}

$userId = intval($_SESSION['user_id']);
$step = intval($_POST['step'] ?? 1);
$isFinalSubmit = !empty($_POST['is_final_submit']);

if ($step === 1) {
    // Step 1: Personal Details
    $fullName = trim($_POST['FullName'] ?? $_POST['username'] ?? '');
    $fatherName = trim($_POST['fathername'] ?? '');
    $motherName = trim($_POST['mothername'] ?? '');
    $grandfatherName = trim($_POST['grandfathername'] ?? '');
    $nativePlace = trim($_POST['nativeplace'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $maritalStatus = trim($_POST['maritalstatus'] ?? '');
    $aadhaar = trim($_POST['aadhaar_number'] ?? '');
    $additionalMobile = trim($_POST['additional_mobile'] ?? '');

    // Validate Aadhaar if provided
    $cleanAadhaar = preg_replace('/[^0-9]/', '', $aadhaar);
    if (!empty($cleanAadhaar)) {
        if (strlen($cleanAadhaar) !== 12 || preg_match('/^[01]/', $cleanAadhaar) || preg_match('/^(\d)\1{11}$/', $cleanAadhaar)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid 12-digit Indian Aadhaar Number (cannot start with 0 or 1, and cannot have all repeating digits).']);
            exit;
        }
    }

    // Validate Additional Mobile if provided
    $cleanAddMobile = preg_replace('/[^0-9]/', '', $additionalMobile);
    if (!empty($cleanAddMobile)) {
        if (strlen($cleanAddMobile) !== 10 || !preg_match('/^[6-9]\d{9}$/', $cleanAddMobile)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid 10-digit Additional Mobile Number starting with 6, 7, 8, or 9.']);
            exit;
        }

        // Check against primary registered phone
        $primaryPhone = $_SESSION['user_phone'] ?? '';
        if (empty($primaryPhone)) {
            $pq = mysqli_query($conn, "SELECT phonenumber FROM user_registrtion WHERE id = $userId LIMIT 1");
            if ($prow = mysqli_fetch_assoc($pq)) {
                $primaryPhone = $prow['phonenumber'];
            }
        }
        if (!empty($primaryPhone) && $cleanAddMobile === preg_replace('/[^0-9]/', '', $primaryPhone)) {
            echo json_encode(['success' => false, 'message' => 'Additional Mobile Number cannot be the same as your primary registered mobile (' . $primaryPhone . ').']);
            exit;
        }
    }

    $age = '';
    if (!empty($dob)) {
        try {
            $dobDate = new DateTime($dob);
            $today = new DateTime('today');
            $age = (string)($dobDate->diff($today)->y);
        } catch (Exception $e) {
            // ignore
        }
    }

    // Handle Profile Picture upload
    $profilePicSql = "";
    $newPicPath = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['profile_picture']['tmp_name'];
        $fileName = $_FILES['profile_picture']['name'];
        $fileSize = $_FILES['profile_picture']['size'];

        if ($fileSize > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Profile picture must be under 5MB in size.']);
            exit;
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExts)) {
            echo json_encode(['success' => false, 'message' => 'Invalid image format. Allowed formats: JPG, PNG, WEBP.']);
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $fileTmp);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowedMimes)) {
            echo json_encode(['success' => false, 'message' => 'Uploaded file is not a valid image.']);
            exit;
        }

        $uploadDir = __DIR__ . '/../uploads/profile_pictures/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $newFileName = 'member_' . $userId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmp, $destPath)) {
            $relPath = 'uploads/profile_pictures/' . $newFileName;
            $newPicPath = $relPath;
            $relPathEsc = mysqli_real_escape_string($conn, $relPath);
            $profilePicSql = ", profile_picture = '$relPathEsc'";
        }
    }

    $nameEsc = mysqli_real_escape_string($conn, $fullName);
    $fatherEsc = mysqli_real_escape_string($conn, $fatherName);
    $motherEsc = mysqli_real_escape_string($conn, $motherName);
    $grandfatherEsc = mysqli_real_escape_string($conn, $grandfatherName);
    $nativeSql = isset($_POST['nativeplace']) ? "nativeplace = '" . mysqli_real_escape_string($conn, $nativePlace) . "'," : "";
    $dobEsc = !empty($dob) ? "'" . mysqli_real_escape_string($conn, $dob) . "'" : "NULL";
    $ageEsc = mysqli_real_escape_string($conn, $age);
    $genderEsc = mysqli_real_escape_string($conn, $gender);
    $maritalEsc = mysqli_real_escape_string($conn, $maritalStatus);
    $aadhaarEsc = mysqli_real_escape_string($conn, $cleanAadhaar);
    $addMobileEsc = mysqli_real_escape_string($conn, $cleanAddMobile);

    $sql = "UPDATE user_registrtion SET 
        username = '$nameEsc',
        fathername = '$fatherEsc',
        mothername = '$motherEsc',
        grandfathername = '$grandfatherEsc',
        $nativeSql
        dob = $dobEsc,
        age = '$ageEsc',
        gender = '$genderEsc',
        maritalstatus = '$maritalEsc',
        aadhaar_number = '$aadhaarEsc',
        additional_mobile = '$addMobileEsc'
        $profilePicSql,
        registration_step = GREATEST(COALESCE(registration_step, 1), 2)
        WHERE id = $userId";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['user_name'] = $fullName;
        echo json_encode([
            'success' => true,
            'message' => 'Personal details saved successfully!',
            'step' => 1,
            'profile_picture' => $newPicPath
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving personal details: ' . mysqli_error($conn)]);
        exit;
    }

} else if ($step === 2) {
    // Step 2: Address Details
    $presentAddress = trim($_POST['presentaddress'] ?? '');
    $presentVillagePost = trim($_POST['presentvillatpost'] ?? '');
    $presentDistrict = trim($_POST['presentdistrict'] ?? '');
    $presentPincode = trim($_POST['presentpincode'] ?? '');
    $presentState = trim($_POST['presentstate'] ?? '');
    $presentCountry = trim($_POST['presentcountry'] ?? 'India');
    $presentIsSame = trim($_POST['presentaddresstopermanent'] ?? '');

    $permanentAddress = trim($_POST['permanentaddress'] ?? '');
    $permanentVillagePost = trim($_POST['permanentvillatpost'] ?? '');
    $permanentDistrict = trim($_POST['permanentdistrict'] ?? '');
    $permanentPincode = trim($_POST['permanentpincode'] ?? '');
    $permanentState = trim($_POST['permanentstate'] ?? '');
    $permanentCountry = trim($_POST['permanentcountry'] ?? 'India');

    if (!empty($presentIsSame)) {
        $permanentAddress = $presentAddress;
        $permanentVillagePost = $presentVillagePost;
        $permanentDistrict = $presentDistrict;
        $permanentPincode = $presentPincode;
        $permanentState = $presentState;
        $permanentCountry = $presentCountry;
    }

    $presAddrEsc = mysqli_real_escape_string($conn, $presentAddress);
    $presVillEsc = mysqli_real_escape_string($conn, $presentVillagePost);
    $presDistEsc = mysqli_real_escape_string($conn, $presentDistrict);
    $presPinEsc = mysqli_real_escape_string($conn, $presentPincode);
    $presStateEsc = mysqli_real_escape_string($conn, $presentState);
    $presCountryEsc = mysqli_real_escape_string($conn, $presentCountry);
    $sameEsc = mysqli_real_escape_string($conn, $presentIsSame);

    $permAddrEsc = mysqli_real_escape_string($conn, $permanentAddress);
    $permVillEsc = mysqli_real_escape_string($conn, $permanentVillagePost);
    $permDistEsc = mysqli_real_escape_string($conn, $permanentDistrict);
    $permPinEsc = mysqli_real_escape_string($conn, $permanentPincode);
    $permStateEsc = mysqli_real_escape_string($conn, $permanentState);
    $permCountryEsc = mysqli_real_escape_string($conn, $permanentCountry);

    $sql = "UPDATE user_registrtion SET 
        presentaddress = '$presAddrEsc',
        presentvillatpost = '$presVillEsc',
        presentdistrict = '$presDistEsc',
        presentpincode = '$presPinEsc',
        presentstate = '$presStateEsc',
        presentcountry = '$presCountryEsc',
        presentaddresstopermanent = '$sameEsc',
        permanentaddress = '$permAddrEsc',
        permanentvillatpost = '$permVillEsc',
        permanentdistrict = '$permDistEsc',
        permanentpincode = '$permPinEsc',
        permanentstate = '$permStateEsc',
        permanentcountry = '$permCountryEsc',
        registration_step = GREATEST(COALESCE(registration_step, 1), 3)
        WHERE id = $userId";

    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            'success' => true,
            'message' => 'Address details saved successfully!',
            'step' => 2
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving address details: ' . mysqli_error($conn)]);
        exit;
    }

} else if ($step === 3) {
    // Step 3: Education & Professional Details
    $qualification = trim($_POST['qulification'] ?? '');
    $qualificationDetails = trim($_POST['qualificationdetails'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');
    $occupationDetails = trim($_POST['occupationdetails'] ?? '');
    $whatsappNumber = trim($_POST['whatsappnumber'] ?? '');
    $messageInfo = trim($_POST['messageinfo'] ?? '');

    $qualEsc = mysqli_real_escape_string($conn, $qualification);
    $qualDetEsc = mysqli_real_escape_string($conn, $qualificationDetails);
    $occEsc = mysqli_real_escape_string($conn, $occupation);
    $occDetEsc = mysqli_real_escape_string($conn, $occupationDetails);
    $waEsc = mysqli_real_escape_string($conn, $whatsappNumber);
    $msgEsc = mysqli_real_escape_string($conn, $messageInfo);

    $finalCompletedSql = $isFinalSubmit ? ", is_profile_completed = 1" : "";

    $sql = "UPDATE user_registrtion SET 
        qulification = '$qualEsc',
        qualificationdetails = '$qualDetEsc',
        occupation = '$occEsc',
        occupationdetails = '$occDetEsc',
        whatsappnumber = '$waEsc',
        messageinfo = '$msgEsc'
        $finalCompletedSql
        WHERE id = $userId";

    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            'success' => true,
            'message' => $isFinalSubmit ? 'Registration completed successfully!' : 'Education & professional details saved!',
            'step' => 3,
            'is_final' => $isFinalSubmit,
            'redirect' => $isFinalSubmit ? 'user-dashboard.php?registered=1' : null
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving details: ' . mysqli_error($conn)]);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid step specified.']);
    exit;
}

