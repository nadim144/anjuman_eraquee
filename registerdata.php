<?php

$link = mysqli_connect("localhost", "root", "", "anjuman_user");

if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$first_name = mysqli_real_escape_string($link, $_REQUEST['fname']);
$middle_name = mysqli_real_escape_string($link, $_REQUEST['mname']);
$last_name = mysqli_real_escape_string($link, $_REQUEST['lname']);
$gender = mysqli_real_escape_string($link, $_REQUEST['gender']);
 

$sql = "INSERT INTO register (fname, mname, lname, gender) VALUES ('$first_name', '$middle_name', '$last_name', '$gender')";
if(mysqli_query($link, $sql)){
    echo "Records added successfully.";
} else{
    echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
}
 
mysqli_close($link);

?>