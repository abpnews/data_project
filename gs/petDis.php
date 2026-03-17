<?php
// Database connection parameters
/*
$sname = "video-cms.cqnwvxrxwjzo.ap-south-1.rds.amazonaws.com";
$uname = "root";
$pwd = "rbKT$9WC2+M3E3C";
$dbname = "data_project";
*/

$host     = "video-cms.cqnwvxrxwjzo.ap-south-1.rds.amazonaws.com";
$username = "root";
$password = "rbKT$9WC2+M3E3C";
$database = "data_project";

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Your SQL query
$sql = "
INSERT INTO petrol_disel_rate 
(data_date, petrol, disel, lat, `long`, code, statecode, statename, cityname, created_at, status)
SELECT 
  CURDATE() AS data_date,
  petrol,
  disel,
  lat,
  `long`,
  code,
  statecode,
  statename,
  cityname,
  CONCAT(CURDATE(), ' 00:00:00') AS created_at,
  status
FROM petrol_disel_rate
WHERE data_date = '2025-10-25'
AND NOT EXISTS (
  SELECT 1 FROM petrol_disel_rate WHERE data_date = CURDATE()
);
";

// Execute query
if (mysqli_query($conn, $sql)) {
    echo "Data copied successfully for today's date.";
} else {
    echo "Error executing query: " . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);
?>

