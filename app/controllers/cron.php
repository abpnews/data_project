<?php

require_once __DIR__.'/../lib/aws/aws-autoloader.php';



class Controller_Cron extends DB
{

    function __construct()
    {
        parent::__construct();
        PHP_SAPI === 'cli' or die('not allowed s');
    }

    function __destruct()
    {
        parent::__destruct();
    }

    //here you can schedule all your cron jobs to run on what time
    function index()
    {
        echo "\nSTART cron jobs==> " .date("Y-m-d H:i:s")."\n";
        //fuel price to get every day 8 hour one minute and so on
        //if (in_array(date('H:i'), array('06:01', '08:01', '13:17'))) {
            shell_exec("php index.php cron getFuelData >/dev/null 2>&1 &");
        //}

        //AQI hourly
        if (in_array(date('i'), array('10'))) {
            shell_exec("php index.php cron getAQI >/dev/null 2>&1 &");
        }
        
        echo "\nEND cron jobs==>";
    }

    function getFuelData()
    {
        //echo "123";
        set_time_limit(0);
        $iocl = new Model_fuel();
        //$iocl->patrolPriceHistory();
        //$d = $iocl->getIOCLpriceByLatLong($params = array());
        $d = $iocl->getIOCLpriceByDistrict();

        if (is_array($d) && count($d) > 0) {
            $today = date('H') >= 6 ? date('Y-m-d') : date('Y-m-d', strtotime('-1 day'));
            $key = "fuel/daily/{$today}/fuel.json";
            $prms = array('bucket' => "devsquad", 'key' => $key, 'body' => json_encode($d));
            push2s3($prms);
            p("Data Updated",false);
        }
    }

    function getAQI()
    {
        set_time_limit(0);
        $pollutionModel = new Model_pollution();
        $d = $pollutionModel->getAQIbyRSS();

        if (is_array($d) && count($d) > 0) {
            $today = date('Y-m-d');
            $key = "aqi/daily/{$today}/aqi.json";
            $prms = array('bucket' => "devsquad", 'key' => $key, 'body' => json_encode($d));
            push2s3($prms);        
            p("AQI Data Updated",false);
        }
    }


}

?>