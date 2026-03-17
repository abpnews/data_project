<?php
$sname = "video-cms.cqnwvxrxwjzo.ap-south-1.rds.amazonaws.com";
$uname = "root";
$pwd = "C2WZsd4ss";
$dbname = "data_project";
$conn = mysqli_connect($sname, $uname, $pwd, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
