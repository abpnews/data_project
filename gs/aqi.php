<?php
include 'conn.php';
include 'vendor/autoload.php';
#require_once __DIR__.'/../../includes/database.php';
use Smalot\PdfParser\Parser;

$yesterday_timestamp = strtotime("yesterday");
$dat=date("Ymd", $yesterday_timestamp);
$url = "https://cpcb.nic.in/upload/Downloads/AQI_Bulletin_".$dat.".pdf";

$saveTo = "AQI_Bulletin_".$dat.".pdf";

if(!file_exists($saveTo)){
$ch = curl_init($url);
$fp = fopen($saveTo, 'w+');

curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_TIMEOUT, 50);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // disable host check
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // disable peer check

if (curl_exec($ch)) {
    echo "File downloaded successfully!";
} else {
    echo "Download failed: " . curl_error($ch);
}

curl_close($ch);
fclose($fp);

}

$parser = new Parser();
$pdf    = $parser->parseFile($saveTo);
$text   = $pdf->getText();
var_dump($text);
// Split into lines
$lines = preg_split('/\r\n|\r|\n/', $text);

$aqiData = [];
foreach ($lines as $line) {
    // Clean up spacing
    $line = trim(preg_replace('/\s+/', ' ', $line));

    // Match lines starting with a serial number
    if (preg_match('/^(\d+)\s+([A-Za-z(). -]+)\s+(Good|Satisfactory|Moderate|Poor|Very Poor|Severe)\s+(\d+)\s+(.+?)\s+(\d+\/\d+)/', $line, $m)) {
        $aqiData[] = [
            "sno"       => (int)$m[1],
            "city"      => trim($m[2]),
            "quality"   => $m[3],
            "aqi_value" => (int)$m[4],
            "pollutant" => trim($m[5]),
            "stations"  => $m[6],
        ];
    }
}

echo "<pre>";
print_r($aqiData);



                            for($i=0;$i<count($aqiData);$i++)
                            {
                               
				    //$city=$aqiData[$i]['city'];
				    echo $city=str_replace(' Very','',$aqiData[$i]['city']);
				    echo "\n";
                                
                                
                                $state=getState($city);
                                
				    echo $quer="insert into state_aqi set `date`='".date("Y-m-d")."',`state`='".$state."',city='".$city."',aqi='".$aqiData[$i]['aqi_value']."',created_at='".date("Y-m-d H:i:s")."',status=1";
				    echo "\n";
                               $conn->query($quer);
                            
                       
                            }

// Output JSON
/*
header('Content-Type: application/json');
echo json_encode($aqiData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
 * 
 */
  
  function getState($city)
  {
      $sname = "video-cms.cqnwvxrxwjzo.ap-south-1.rds.amazonaws.com";
$uname = "root";
$pwd = "C2WZsd4ss";
$dbname = "data_project";
$conndb = mysqli_connect($sname, $uname, $pwd, $dbname);

            $sql = "select state from state_aqi where city='".$city."' limit 0,1";
          $result = $conndb->query($sql);
          $state='';
      if ($result->num_rows > 0) 
      {
        // output data of each row
        while($row = $result->fetch_assoc())
        {
            $state=$row["state"];
          
	}

	if($city=='Aurangabad (Bihar)')
        {
            $state='Bihar';
        }
      } 
      return $state;
  }                          
                            
?>
