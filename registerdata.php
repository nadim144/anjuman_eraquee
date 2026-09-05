<?php
require_once __DIR__ . '/db.php';
$link = get_db_connection();

if ($link === false) {
    die("ERROR: Could not connect to MySQL database. Please make sure MySQL is running in XAMPP Control Panel and database 'codecxss_anjuman' or 'anjuman_user' exists.");
}

$name = mysqli_real_escape_string($link, $_REQUEST['FullName'] ?? $_REQUEST['username'] ?? '');
$father_name = mysqli_real_escape_string($link, $_REQUEST['fathername'] ?? '');
$mother_name = mysqli_real_escape_string($link, $_REQUEST['mothername'] ?? '');
$grandfather_name = mysqli_real_escape_string($link, $_REQUEST['grandfathername'] ?? '');
$native_place = mysqli_real_escape_string($link, $_REQUEST['nativeplace'] ?? '');
$dob = mysqli_real_escape_string($link, $_REQUEST['dob'] ?? '');
$age = mysqli_real_escape_string($link, $_REQUEST['age'] ?? '');

// Calculate age if DOB provided
if (!empty($dob)) {
    try {
        $dobDate = new DateTime($dob);
        $today = new DateTime('today');
        $calcAge = $dobDate->diff($today)->y;
        $age = (string)$calcAge;
    } catch (Exception $e) {
        // keep submitted age if parsing fails
    }
}

$gender = mysqli_real_escape_string($link, $_REQUEST['gender'] ?? '');
$marital_status = mysqli_real_escape_string($link, $_REQUEST['maritalstatus'] ?? '');
$present_address = mysqli_real_escape_string($link, $_REQUEST['presentaddress'] ?? '');
$present_village_post = mysqli_real_escape_string($link, $_REQUEST['presentvillatpost'] ?? '');
$present_district = mysqli_real_escape_string($link, $_REQUEST['presentdistrict'] ?? '');
$present_pincode = mysqli_real_escape_string($link, $_REQUEST['presentpincode'] ?? '');
$present_state = mysqli_real_escape_string($link, $_REQUEST['presentstate'] ?? '');
$present_country = mysqli_real_escape_string($link, $_REQUEST['presentcountry'] ?? '');
$present_address_to_permanent_address = mysqli_real_escape_string($link, $_REQUEST['presentaddresstopermanent'] ?? '');
$permanent_address = mysqli_real_escape_string($link, $_REQUEST['permanentaddress'] ?? '');
$permanent_village_post = mysqli_real_escape_string($link, $_REQUEST['permanentvillatpost'] ?? '');
$permanent_district = mysqli_real_escape_string($link, $_REQUEST['permanentdistrict'] ?? '');
$permanent_pincode = mysqli_real_escape_string($link, $_REQUEST['permanentpincode'] ?? '');
$permanent_state = mysqli_real_escape_string($link, $_REQUEST['permanentstate'] ?? '');
$permanent_country = mysqli_real_escape_string($link, $_REQUEST['permanentcountry'] ?? '');
$email = mysqli_real_escape_string($link, $_REQUEST['email'] ?? '');
$phonenumber = mysqli_real_escape_string($link, $_REQUEST['phonenumber'] ?? '');
$whatsappnumber = mysqli_real_escape_string($link, $_REQUEST['whatsappnumber'] ?? '');
$qualification = mysqli_real_escape_string($link, $_REQUEST['qulification'] ?? '');
$qualification_details = mysqli_real_escape_string($link, $_REQUEST['qualificationdetails'] ?? '');
$occupation = mysqli_real_escape_string($link, $_REQUEST['occupation'] ?? '');
$occupation_details = mysqli_real_escape_string($link, $_REQUEST['occupationdetails'] ?? '');
$message = mysqli_real_escape_string($link, $_REQUEST['messageinfo'] ?? '');

$raw_password = $_REQUEST['password'] ?? '';
$confirm_password = $_REQUEST['confirm_password'] ?? '';

if (!empty($raw_password) && $raw_password !== $confirm_password) {
    echo "<script>alert('Passwords do not match. Please try again.'); window.history.back();</script>";
    exit;
}

$password_hash = !empty($raw_password) ? password_hash($raw_password, PASSWORD_BCRYPT) : '';
$password_escaped = mysqli_real_escape_string($link, $password_hash);

// Check if user exists by email or phone
$checkexistinguser = "SELECT * FROM user_registrtion WHERE (email='$email' AND email != '') OR (phonenumber='$phonenumber' AND phonenumber != '') LIMIT 1";
$res = mysqli_query($link, $checkexistinguser);
if ($res && mysqli_num_rows($res) > 0) {
    echo "<script>alert('A user with this Email or Mobile Number is already registered. Please login.'); document.location.href='user-login.php';</script>";
    exit;
}

$sql = "INSERT INTO user_registrtion (
    username, fathername, mothername, grandfathername, nativeplace,
    dob, age, gender, maritalstatus, presentaddress, presentvillatpost,
    presentdistrict, presentpincode, presentstate, presentcountry,
    presentaddresstopermanent, permanentaddress, permanentvillatpost,
    permanentdistrict, permanentpincode, permanentstate, permanentcountry,
    email, phonenumber, whatsappnumber, qulification, qualificationdetails,
    occupation, occupationdetails, messageinfo, password, created_at
) VALUES (
    '$name', '$father_name', '$mother_name', '$grandfather_name', '$native_place',
    " . (!empty($dob) ? "'$dob'" : "NULL") . ", '$age', '$gender', '$marital_status',
    '$present_address', '$present_village_post', '$present_district', '$present_pincode',
    '$present_state', '$present_country', '$present_address_to_permanent_address',
    '$permanent_address', '$permanent_village_post', '$permanent_district', '$permanent_pincode',
    '$permanent_state', '$permanent_country', '$email', '$phonenumber', '$whatsappnumber',
    '$qualification', '$qualification_details', '$occupation', '$occupation_details',
    '$message', '$password_escaped', NOW()
)";

if (mysqli_query($link, $sql)) {
    echo "<script>alert('Your Registration is Successful! Please login with your Mobile/Email and Password.'); document.location.href='user-login.php';</script>";
} else {
    echo "ERROR: Could not able to execute query. " . mysqli_error($link);
}

mysqli_close($link);
?>