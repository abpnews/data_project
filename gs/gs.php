<?php
    include 'conn.php';
    include 'vendor/autoload.php';
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseContent(file_get_contents("https://ibjarates.com/ratespdf/Daily%20Opening%20and%20Closing%20Market%20Rate.pdf"));
    
    $text = $pdf->getPages()[0]->getDataTm();    
    $arr=[]; $tt=[]; $filterarr_raw=[]; $filterarr=[];
    foreach ($text as $key=>$value){
        $tt[]=$value['1'];
    }
    function validateDate($date, $format = 'd-M-y'){
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    foreach ($tt as $key=>$value){
        if(validateDate($value) == 1){
            $arr[] =  $key;            
        }
    }
    $bb = count($tt)-1;
    array_push($arr,$bb);
    $indexes=array_keys($tt);
    for($i=0;$i<count($arr)-1;$i++){
        $start=array_search($arr[$i],$indexes);
        $length=array_search($arr[$i+1],$indexes)-$start;
        $filterarr_raw[] = array_slice($tt,$start,$length);
    }    
    $filterarr = array_reverse($filterarr_raw);
    $success = 0; $fail = 0; $duplicate=0;                        
        if(count($filterarr)>0){
            for($a=0;$a<count($filterarr);$a++){                
                if(count($filterarr[$a]) == 13){
                  $date_rate = date("Y-m-d",strtotime($filterarr[$a][0]));
                  $sqlselect = "SELECT id FROM gold_silver_rates Where date_rate = '".$date_rate."'";
                  $resultselect = $conn->query($sqlselect);
                  if ($resultselect->num_rows < 1) {
                    $sqlinsert = "INSERT INTO  gold_silver_rates (date_rate, gold_999_am_price, gold_999_pm_price, gold_995_am_price,
                    gold_995_pm_price, gold_916_am_price, gold_916_pm_price, gold_750_am_price, gold_750_pm_price, gold_585_am_price,
                    gold_585_pm_price, silver_999_am_price, silver_999_pm_price, created_at)
                    VALUES ('".$date_rate."', '".$filterarr[$a][1]."', '".$filterarr[$a][2]."', '".$filterarr[$a][3]."', '".$filterarr[$a][4]."',
                    '".$filterarr[$a][5]."', '".$filterarr[$a][6]."', '".$filterarr[$a][7]."', '".$filterarr[$a][8]."',
                     '".$filterarr[$a][9]."', '".$filterarr[$a][10]."', '".$filterarr[$a][11]."',
                      '".$filterarr[$a][12]."', NOW())";
                      if ($conn->query($sqlinsert) === TRUE) {
                        $success = $success+1;                        
                      }else{
                        $fail = $fail+1;
                      }
                  }else{
                    $duplicate = $duplicate+1;
                  }
                }                
            }
            if($duplicate > 0){
                echo "Record Already Present In Table : ".$duplicate; echo "<br/>";
             }
            
            if($success > 0){
               echo "Record Inserted : ".$success; echo "<br/>";
            }
            if($fail > 0){
                echo "Record failed to Insert : ".$fail;
             }
        }
?>