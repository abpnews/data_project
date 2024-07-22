<?php
include 'conn.php';
function fetchHTML($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    $output = curl_exec($ch);
    if ($output === false) {
        echo "cURL Error: " . curl_error($ch);
    }
    curl_close($ch);
    return $output;
}

$url = "https://www.goldpricesindia.com/";

$html = fetchHTML($url);

if ($html !== false) {
    $filename = "captured_content.html";
    file_put_contents($filename, $html);
    
    $saved_html = file_get_contents($filename);

    $dom = new DOMDocument();
    @$dom->loadHTML($saved_html); 
    
    $city_prices = array();

    $inputs = $dom->getElementsByTagName('input');
    foreach ($inputs as $input) {
        $name = $input->getAttribute('name');
        $value = $input->getAttribute('value');
        
        preg_match('/^(gold|silver)([a-z\-]+)(\d*)$/', $name, $matches);
        if (count($matches) === 4) {
            $type = $matches[1]; // "gold" or "silver"
            $city = ucfirst($matches[2]); // City name, capitalized
            $suffix = $matches[3];
            
            if ($suffix === '22') {
                $key = strtolower($type) . '22'; 
            } else {
                $key = strtolower($type); 
            }
            
            $city_prices[$city][$key] = $value;
        }
    }

    // HTML table 
    echo "<table border='1'>";
    echo "<thead><tr><th>City</th><th>Gold Price</th><th>Gold22 Price</th><th>Silver Price</th></tr></thead>";
    echo "<tbody>";
    foreach ($city_prices as $city => $prices) 
    {
        $gold_price = isset($prices['gold']) ? $prices['gold'] : '-';
        $gold22_price = isset($prices['gold22']) ? $prices['gold22'] : '-';
        $silver_price = isset($prices['silver']) ? $prices['silver'] : '-';
        echo "<tr><td>$city</td><td>$gold_price</td><td>$gold22_price</td><td>$silver_price</td></tr>";
        
        echo $sqlinsert = "INSERT INTO  city_gold_sliver set data_date='".date("Y-m-d")."',city_state='".$city."',gold_24='".$gold_price."',gold_22='".$gold22_price."',sliver='".$silver_price."',status=1,created_at= NOW()";
        echo "<br>";
        if ($conn->query($sqlinsert) === TRUE)
        {
                        echo "success";                        
        }
        
    }
    echo "</tbody>";
    echo "</table>";

} else {
    echo "Failed to fetch HTML content from $url.";
}
?>