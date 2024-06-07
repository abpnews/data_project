<?php
require_once __DIR__.'/../includes/database.php';
class Model_pollution
{

    protected $retryTimes = 4;
    protected $debug = 0;


    function getAQIbyRSS()
    {
        /*
        $citydata = getcityData();
        $res = [];
        $citydata = json_decode($citydata, 1);

        $ct = [];
        foreach ($citydata as $d) {
            $ct[$d['city']] = $d;
        }

        $citydata = $ct;
        */

        // echo "123";
        $res = makeCURLRequest(array("url" => "https://app.cpcbccr.com/caaqms/city_rss_feed"));
         $html = $res['data'];
       // die();
        $xml = simplexml_load_string($html);
        $xml = json_decode(json_encode($xml), true);
       // p($xml);
        $citi = array();
        if(isset($xml['state'])){
            foreach ($xml['state'] as $st) {
                $stName = str_replace('_',' ',$st['@attributes']['id']);
                foreach ($st['Pollution-Index'] as $k => $ct1) {
                    if ($k === '@attributes') {
                        $cityName = str_replace('_',' ',$ct1['id']);
                        $aqi = $ct1['aqi'];
                        //$citi[$st['@attributes']['id']][$ct1['id']] = $ct1['aqi'];
                    } else {
                        $cityName = str_replace('_',' ',$ct1['@attributes']['id']);
                        $aqi = $ct1['@attributes']['aqi'];
                        //print "$k\nELSE\n";print_r($ct1);
                        //foreach($ct1 as $ct2){
                        //$citi[$st['@attributes']['id']][$ct1['@attributes']['id']] = $ct1['@attributes']['aqi'];
                        //}
                    }
                    
                   // print_r($aqi);
                    $citi[$stName][$cityName] = $aqi;
                }
            }
        }
                  //echo "<pre>";
                   //print_r($citi);
                   
                   
                   foreach($citi as $key => $value)
                   {
                       
                            foreach($value as $cname => $aqi)
                            {
                                $dbMod=new DB();
                            echo $quer="insert into state_aqi set `date`='".date("Y-m-d")."',`state`='".$key."',city='".$cname."',aqi='".$aqi."',created_at='".date("Y-m-d H:i:s")."',status=1";
                            $dbMod->query($quer);
                            unset($dbMod);
                       
                            }
                       
                            
                       
                   }
                   
                   
        return $citi;
    }
}
