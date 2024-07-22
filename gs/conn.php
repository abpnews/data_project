<?php
$sname = "video-cms.cqnwvxrxwjzo.ap-south-1.rds.amazonaws.com";
$uname = "root";
$pwd = "rbKT$9WC2+M3E3C";
$dbname = "data_project";
$conn = mysqli_connect($sname, $uname, $pwd, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
