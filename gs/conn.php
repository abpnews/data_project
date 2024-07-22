<?php
$sname = "127.0.0.1";
$uname = "root";
$pwd = "12345678";
$dbname = "tool";
$conn = mysqli_connect($sname, $uname, $pwd, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
