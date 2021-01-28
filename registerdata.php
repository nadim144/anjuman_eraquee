<?php 
$link = mysqli_connect("localhost", "codecxss_anjuman", "anjuman!@#2021", "codecxss_anjuman");
// $link = mysqli_connect("localhost", "root", "", "anjuman_user");

if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$name = mysqli_real_escape_string($link, $_REQUEST['username']);
$father_name = mysqli_real_escape_string($link, $_REQUEST['fathername']);
$mother_name = mysqli_real_escape_string($link, $_REQUEST['mothername']);
$grandfather_name = mysqli_real_escape_string($link, $_REQUEST['grandfathername']);
$native_place = mysqli_real_escape_string($link, $_REQUEST['nativeplace']);
$age = mysqli_real_escape_string($link, $_REQUEST['age']);
$gender = mysqli_real_escape_string($link, $_REQUEST['gender']);
$marital_status = mysqli_real_escape_string($link, $_REQUEST['maritalstatus']);
$present_address = mysqli_real_escape_string($link, $_REQUEST['presentaddress']);
$present_village_post = mysqli_real_escape_string($link, $_REQUEST['presentvillatpost']);
$present_district = mysqli_real_escape_string($link, $_REQUEST['presentdistrict']);
$present_pincode = mysqli_real_escape_string($link, $_REQUEST['presentpincode']);
$present_state = mysqli_real_escape_string($link, $_REQUEST['presentstate']);
$present_country = mysqli_real_escape_string($link, $_REQUEST['presentcountry']);
$present_address_to_permanent_address = mysqli_real_escape_string($link, $_REQUEST['presentaddresstopermanent']);
$permanent_address = mysqli_real_escape_string($link, $_REQUEST['permanentaddress']);
$permanent_village_post = mysqli_real_escape_string($link, $_REQUEST['permanentvillatpost']);
$permanent_district = mysqli_real_escape_string($link, $_REQUEST['permanentdistrict']);
$permanent_pincode = mysqli_real_escape_string($link, $_REQUEST['permanentpincode']);
$permanent_state = mysqli_real_escape_string($link, $_REQUEST['permanentstate']);
$permanent_country = mysqli_real_escape_string($link, $_REQUEST['permanentcountry']);
$email = mysqli_real_escape_string($link, $_REQUEST['email']);
$phonenumber = mysqli_real_escape_string($link, $_REQUEST['phonenumber']);
$whatsappnumber = mysqli_real_escape_string($link, $_REQUEST['whatsappnumber']);
$qualification = mysqli_real_escape_string($link, $_REQUEST['qulification']);
$qualification_details = mysqli_real_escape_string($link, $_REQUEST['qualificationdetails']);
$occupation = mysqli_real_escape_string($link, $_REQUEST['occupation']);
$occupation_details = mysqli_real_escape_string($link, $_REQUEST['occupationdetails']);
$message = mysqli_real_escape_string($link, $_REQUEST['messageinfo']);
 


$checkexistinguser="select * from user_registrtion where (email='$email' or phonenumber='$phonenumber');";
$res=mysqli_query($link, $checkexistinguser);
if (mysqli_num_rows($res) > 0) 
{
        
    $row = mysqli_fetch_assoc($res);
    if($email==isset($row['email']) or $phonenumber==isset($row['phonenumber']))
    {
        // echo '<script>alert("user already exists") </script>';
        echo "<script>if(confirm('user already exists'))
        {
            document.location.href='registration.html'
        };</script>";
    }       
}

$sql = "INSERT INTO user_registrtion (username, fathername, mothername, grandfathername, nativeplace,
age, gender, maritalstatus, presentaddress, presentvillatpost, presentdistrict, presentpincode, presentstate, presentcountry, presentaddresstopermanent,
permanentaddress, permanentvillatpost, permanentdistrict, permanentpincode, permanentstate, permanentcountry, email, phonenumber, whatsappnumber,
qulification, qualificationdetails, occupation, occupationdetails, messageinfo) VALUES ('$name', '$father_name', '$mother_name', '$grandfather_name',
'$native_place', '$age', '$gender', '$marital_status', '$present_address', '$present_village_post', '$present_district', '$present_pincode',
'$present_state', '$present_country', '$present_address_to_permanent_address', '$permanent_address', '$permanent_village_post','$permanent_district',
'$permanent_pincode', '$permanent_state', '$permanent_country', '$email', '$phonenumber', '$whatsappnumber', '$qualification', '$qualification_details',
'$occupation', '$occupation_details', '$message')";


if(mysqli_query($link, $sql)){
if(mysqli_query($link, $sql))
{
    //echo "Records added successfully.";
    // echo '<script>alert("Records added successfully") </script>';
    echo "<script>if(confirm('Your Record Successfully Inserted. Now Login'))
    {
        document.location.href='registration.html'
    };</script>";
} 
else
{
    echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
}

mysqli_close($link);
?>