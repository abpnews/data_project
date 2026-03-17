<?php error_reporting(E_ALL); // Report all errors
    error_reporting(E_ERROR | E_WARNING); // Report fatal errors and warnings
    error_reporting(1); // Turn off all error reporting
    /*
// Database credentials
$host = "localhost";
$user = "root";
$pass = "Abp@12345678$";   // change if needed
$dbname = "fuel_data";

// Create MySQL connection
$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("DB Connection failed: " . mysqli_connect_error());
}
*/
include('conn.php');
// Common cURL function

function fetchHTML($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT; Win64; x64)"
    ]);
    $html = curl_exec($ch);
    curl_close($ch);
    return $html;
}

// Common scraper function
function scrapeFuelData($url, $type, $conn) {
    $html = fetchHTML($url);
    
    if (!$html) {
        echo "Failed to fetch page for $type.\n";
        return;
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    // Extract update date
    $updateDate = date('Y-m-d');

    // Extract table rows
    $rows = $xpath->query("//table//tr");

    foreach ($rows as $index => $row) {
        if ($index === 0) continue; // skip header row

        $cols = $row->getElementsByTagName("td");
        if ($cols->length >= 2) {
            $city  = mysqli_real_escape_string($conn, trim($cols->item(0)->nodeValue));
            $price = mysqli_real_escape_string($conn, preg_replace('/[^0-9.]/', '', trim($cols->item(1)->nodeValue)));

            if (!empty($city) && !empty($price)) {
                if ($type === 'diesel') {
                    $sql = "INSERT INTO fuel_prices_diesel (city, diesel_price, updated_at)
                            VALUES ('$city', '$price', '$updateDate')";
                } else {
                    $sql = "INSERT INTO fuel_prices_petrol (city, petrol_price, updated_at)
                            VALUES ('$city', '$price', '$updateDate')";
                }

                if (!mysqli_query($conn, $sql)) {
                    echo "Error inserting $city ($type): " . mysqli_error($conn) . "\n";
                }
            }
        }
    }

    echo ucfirst($type) . " prices inserted successfully!\n";
}



// URLs
$dieselURL = "https://www.hindustantimes.com/fuel-prices/diesel-rates-city-wise";
$petrolURL = "https://www.hindustantimes.com/fuel-prices/petrol-rates-city-wise";


// // Scrape and save both
$a=scrapeFuelData($dieselURL, 'diesel', $conn);

$b=scrapeFuelData($petrolURL, 'petrol', $conn);

//$conn->close();

//echo " All data scraped and saved successfully!\n";
// echo $sql = "INSERT INTO petrol_disel_rate (cityname, petrol, disel, created_at)
// SELECT 
//     p.city AS cityname,
//     REPLACE(p.petrol_price, '₹', '') AS petrol_price,
//     REPLACE(d.diesel_price, '₹', '') AS diesel_price,
//     NOW() AS created_at
// FROM fuel_prices_petrol AS p
// INNER JOIN fuel_prices_diesel AS d
//     ON p.city = d.city
// ";

$date = date('Y-m-d');


$temp=[];
$i=1;
$sql ="SELECT  id,  city,  petrol_price,updated_at   FROM    fuel_prices_petrol where updated_at = '$date'";
$q=mysqli_query($conn, $sql);
while($rest = mysqli_fetch_assoc($q)){
 $diesel ="SELECT    city,  diesel_price,updated_at  FROM    fuel_prices_diesel where updated_at = '$date' and city='$rest[city]'";
$dsql =mysqli_query($conn, $diesel);
$redsl=mysqli_fetch_assoc($dsql);
$temp[$i]=[
    "id"=>$rest['id'],
    "petrol_price" =>$rest['petrol_price'],
    "diesel_price" =>$redsl['diesel_price'],
    "city" =>$rest['city'],
    "date" =>$rest['updated_at']
];
$i++;
}


foreach($temp as $id=>$data){
    $city=$data['city'];
    $petrol_price =$data['petrol_price'];
    $diesel_price =$data['diesel_price'];
    $date =$data['date'];
    
     $sql1 = "SELECT * FROM city_master WHERE city = '$city' or city_alias  = '$city'";echo "</br>";
        $res1 = mysqli_query($conn, $sql1);
       $co=mysqli_num_rows($res1);
       if($co > 0){
        $row_res = mysqli_fetch_assoc($res1);
       $cityname = mysqli_real_escape_string($conn, $row_res['city']);echo "</br>";
      echo $citycode =$row_res['city_code'];
      echo  $sql_state ="select statename,state_code from city_master where city='$cityname' or city_alias  = '$cityname'";echo "</br>";
             $res_state = mysqli_query($conn, $sql_state);
             $co_num =mysqli_num_rows($res_state);
           
            if($co_num > 0)
{       $row_state = mysqli_fetch_assoc($res_state);
          echo $state_name= $row_state['statename'];echo "</br>";
        echo  $state_code =$row_state['state_code'];echo "</br>";
         echo $sql_insert = "INSERT INTO petrol_disel_rate (cityname, petrol,disel,statename,statecode,code, created_at,data_date)
                       VALUES ('$cityname', '$petrol_price','$diesel_price','$state_name','$state_code' ,'$citycode', '$date','$date')";
 mysqli_query($conn, $sql_insert);
     }  
  }
}



?>

