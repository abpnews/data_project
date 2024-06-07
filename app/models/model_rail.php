<?php
require_once __DIR__.'/../includes/class.cropcanvas.php';
require_once __DIR__.'/../lib/simple_html_dom.php';
class Model_rail {

    protected $retryTimes = 1;
    protected $debug = 1;
    private $domain;

    function __construct()
    {
        $this->domain = 'https://indianrail.gov.in';
    }

    public function getPNRData($params) {
        $this->retryTimes = 2;
        $params['api'] = isset($params['api']) ? $params['api'] : 'BOTH';
        $this->debug = isset($params['debug']) ? intval($params['debug']) : false;
        
        
        $data = $this->getPNRDataJSON($params);
        
        return $data;
    }

    public function getTrainScheduleData($params) {
        $this->retryTimes = 2;
        $params['api'] = isset($params['api']) ? $params['api'] : 'BOTH';
        $this->debug = isset($params['debug']) ? intval($params['debug']) : false;
        
        $data = $this->getTrainScheduleDataJSON($params);
        
        return $data;
    }

    public function crackCaptcha() {
        $ret = array('error' => 1, 'errorcode' => "Generic error", 'data' => array());
        $p = array();
        $p['url'] = "https://www.indianrail.gov.in/enquiry/captchaDraw.png?".time();
        $p['method'] = 'GET';
        //$p['return_header'] = 0;
        $res = makeCURLRequest($p);
        if ($res['error'] == 1 || empty($res['data'])) {
            $ret['errorcode'] = isset($res['errorcode']) ? $res['errorcode'] : 'Captcha URL giving error ';
            return $ret;
        }

        $newheaders = array();
        $code = -1;
        if (!empty($res['headers'])) {
            foreach ($res['headers'] as $h) {
                if (stripos(strtolower($h), 'et-Cookie:')) {
                    $newheaders[] = str_replace('Set-Cookie: ', '', current(explode(';', $h)));
                }
            }
            if (!empty($newheaders)) {
                $newheaders = array_reverse($newheaders);
                $newheaders = array('Cookie: ' . implode('; ', $newheaders) . ';');
            }
        }

        if (!empty($newheaders)) {
            $railCaptcha = new Model_railCaptcha();
            $railCaptcha->debug = $this->debug;
            try {
                $code = intval($railCaptcha->getCaptchaCode($res['data']));
            } catch (Exception $e) {
                $code = -1;
            }
        }

        if($this->debug){
            p($res,false);
        }

        if ($code >= 0) {
            $ret['error'] = 0;
            $ret['errorcode'] = '';
            $ret['data'] = array('newheaders' => $newheaders, 'code' => $code);
        } else {
            $ret['errorcode'] = 'Captcha crack failed';
        }

        return $ret;
    }

    public function getPNRDataJSON($params, $flag=false) {
        $ret = array('error' => 1, 'errorcode' => 1, 'data' => array(), 'params' => $params);

        $captchaCode = $this->crackCaptcha();
        if ($captchaCode['error'] == 1) {
            if ($this->debug) {
                echo $captchaCode['errorcode'];
            }
            $captchaCode['errorcode'] = 8;
            return $captchaCode;
        }
        $code = $captchaCode['data']['code'];
        $newheaders = $captchaCode['data']['newheaders'];

        //$newheaders[] = "rcheck: 03AF6jDqWUqPGNyxwVYxCkBiaCsUXvDD5bdno-l1qPsmck9uOMAVyOAZ4RPh8hyQFxyDFiqpFDRmtnupHw9i1fqNpUoL5lxcUO566PJzUuKY62pCowBKmC46HNWpGyx2NrtsBjGwOT38QMZEdH6Hn11ib-OXSfA4aior35c3tDBcMSuSIto_MDacwdgVFJdhFgMTG79tr9wj2euhzB8uU1zQhpQP_9j7UIDyI5j9EtGT_qERKHzZ5swAXNX5fDsU4CtOrzbPd_5dLjyzNcqHA5E0ikw88EVX1ql0wUMneJMiJ4nN_H0NM8-nrSt4E2uczJIJR61ycAIlEj";
        //$newheaders[] = "greq: ".(microtime(true)*100)."0";
        
        usleep(10);
        $p = array();        
        $p['url'] = "https://www.indianrail.gov.in/enquiry/CommonCaptcha?inputCaptcha={$code}&inputPnrNo={$params['pnr']}&inputPage=PNR&language=en";
        $p['method'] = 'GET';
        $p['oheaders'] = array(
            "rcheck: 03AF6jDqUw4IxJ3M5ViIX9LH46DczA3RUjhNhinncYQ6yI0ScbfcbmjPE3YTsCaQEj4ucIHBi4EqBBtntoyP6aV6c-tnyAg31rvPjjVRfZkZMkA4W7aAPHIEYbZO5UftjIbgocUmbQzZNi2dXU_iGxyjBjGpK2qj2_h1EwCVUd6wl0IyTOmqNV4NFd4assZava1osHgP7AtT3h49W_WqM0qw6MIPXB3zu7Vey42nP-n0sLMYybPAq4Gdpabcxalac5Du9w9SnYTJdMaMnWHnq6xRSPqx8wjon3v4qZzoo3JkASKc2NFX5iGflIeSD6WOybQ2FYKd9eyGqD",//.generateRandomString(335),
            "user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36",
            "Content-Type: application/json; charset=UTF-8",
            "greq: ".(microtime(true) * 1000)."",
            );
        $p['oheaders'] = array_merge($p['oheaders'], $newheaders);
        $res = makeCURLRequest($p);
        if ($res['error'] == 1) {
            if ($this->debug) {
                echo $res['errorcode'];
            }
            $ret['errorcode'] = 7;
            return $ret;
        }

        //p($res['data']);
        if (isset($res['data']) && !empty($res['data'])) {
            $params['pnr'] = isset($params['pnr']) ? $params['pnr'] : '';
            $params['retry'] = isset($params['retry']) ? $params['retry'] : 1;
            $d = json_decode($res['data'], true);
            if ($d === null && json_last_error() !== JSON_ERROR_NONE) {
                $ret['errorcode'] = 9;
                return $ret;
            }
            if (isset($d['errorMessage']) && !empty($d['errorMessage'])) {
                $error = 1;
                $errorcode = $d['errorMessage']; 
                if (stripos($errorcode, '134381610') !== false || stripos($errorcode, 'FLUSHED') !== false) { //flushed or not generated
                    $error = 5;
                    $errorcode = 2;
                } elseif (stripos($errorcode, '90016') !== false || stripos($errorcode, '10157926') !== false) { // offilne
                    $errorcode = 3;
                } elseif (stripos($errorcode, '134316306') !== false) { // PNR ourside ARP, try SPECIAL api
                    $errorcode = 1;
                } elseif (stripos($errorcode, 'PNR No. is not valid') !== false || stripos($errorcode, '134318522') !== false) {
                    $error = 5;
                    $errorcode = 4;
                } elseif (stripos($errorcode, 'Captcha not matched') !== false) {
                    $errorcode = 5;
                } elseif (stripos($errorcode, 'Session out or Bot attack') !== false) {
                    $errorcode = 5;
                } elseif (stripos($errorcode, 'Bot') !== false) {
                    $errorcode = 5;
                } else {
                    $errorcode = 1;
                }

                $data = array();
            } elseif (isset($d['flag']) && strtolower($d['flag']) == 'no') {
                $error = 1;
                $errorcode = 5;
                $data = array();
            } else if (isset($d['trainNumber']) && !empty($d['trainNumber'])) {
                //p($d);
                $data = array(
                    'pnr' => $d['pnrNumber'],
                    'eticket' => '',
                    'trin_no' => (isset($d['trainNumber']) ? $d['trainNumber'] : ''),
                    'train_name' => $d['trainName'],
                    'boarding_date' => isset($d['dateOfJourney']['orig_year']) ? strtotime($d['dateOfJourney']['orig_year'] . '-' . $d['dateOfJourney']['orig_month'] . '-' . $d['dateOfJourney']['orig_day'] . ' 00:00:00') : strtotime($d['dateOfJourney']),
                    'from' => $d['sourceStation'],
                    'to' => $d['destinationStation'],
                    'boarding_to' => $d['reservationUpto'],
                    'boarding_from' => $d['boardingPoint'],
                    'class' => $d['journeyClass'],
                    'total_fair' => $d['bookingFare'],
                    'chart_status' => strtoupper($d['chartStatus']),
                    'note' => isset($d['trainCancelStatus']) ? $d['trainCancelStatus'] : ''
                );

                $isconfirmed = false;
                $passengers = array();
                if (isset($d['passengerList'])) {
                    foreach ($d['passengerList'] as $k => $p) {
                        $booking_status_orig = $p['bookingStatus'] . '/' . $p['bookingCoachId'] . '/' . $p['bookingBerthNo'];
                        $current_status_orig = $p['currentStatus'] . '/' . $p['currentCoachId'] . '/' . $p['currentBerthNo'];
                        $sameStatus = ($p['bookingStatus'] == $p['currentStatus']) ? 1 : 0;
                        $p['bookingStatus'] = in_array($p['bookingStatus'], array('WEBC')) ? 'CAN' : $p['bookingStatus'];
                        $p['currentStatus'] = in_array($p['currentStatus'], array('WEBC')) ? 'CAN' : $p['currentStatus'];

                        $isconfirmed = ($p['currentStatus'] == 'CNF' || $p['currentStatus'] == 'RAC') ? true : $isconfirmed;
                        $p['bookingStatus'] = (!empty($p['bookingCoachId']) && $p['bookingStatus'] == 'RAC') ? 'R' : $p['bookingStatus'];
                        $p['currentStatus'] = (!empty($p['currentCoachId']) && $p['currentStatus'] == 'RAC') ? 'R' : $p['currentStatus'];
                        $p['bookingStatus'] = $p['bookingStatus'] == 'R' ? 'R' : (($p['bookingStatus'] == 'CNF' && !empty($p['bookingBerthNo']) ) ? '' : "{$p['bookingStatus']},");
                        $p['currentStatus'] = $p['currentStatus'] == 'R' ? 'R' : (($p['currentStatus'] == 'CNF' && !empty($p['currentBerthNo']) ) ? '' : "{$p['currentStatus']},");
                        $booking_status = $p['bookingStatus'] . (!empty($p['bookingCoachId']) ? $p['bookingCoachId'] . (',') : '') . ((intval($p['bookingBerthNo']) > 0) ? $p['bookingBerthNo'] : '');
                        $current_status = $p['currentStatus'] . (!empty($p['currentCoachId']) ? $p['currentCoachId'] . (',') : '') . ((intval($p['currentBerthNo']) > 0) ? $p['currentBerthNo'] : '');
                        $current_status = trim(rtrim($current_status, ','));
                        $booking_status = trim(rtrim($booking_status, ','));

                        $current_status = (($current_status == '' || $current_status == 'CNF') && $sameStatus) ? $booking_status : $current_status;

                        $current_status = $current_status == 'CNF' ? "Confirmed" : $current_status;
                        $booking_status = $booking_status == 'CNF' ? "Confirmed" : $booking_status;
                        $current_status = $current_status == 'CAN' ? "Can/Mod" : $current_status;
                        $booking_status = $booking_status == 'CAN' ? "Can/Mod" : $booking_status;

                        $current_status = str_replace(array('WL', 'PQ', 'RL'), array('W/L', '', ''), $current_status);
                        $booking_status = str_replace('WL', 'W/L', $booking_status);
                        $seatno = '';
                        if ((stripos($current_status, 'RAC') === false && stripos($current_status, 'W/L') === false) && $isconfirmed) {
                            $seatno = intval(@end(explode(',', $current_status)));
                        }

                        $isGarib = (isset($current_status[0]) && (strtolower($current_status[0]) == 'g' || strtolower($current_status[0] . $current_status[1]) == 'rg')) ? 1 : 0;
                        $seat_position = $this->seatPosition($seatno, $data['class'], $isGarib);
                        $passengers[] = array('passenger' => 'Passenger ' . $p['passengerSerialNumber'], 'booking_status' => $booking_status, 'current_status' => $current_status, 'booking_status_orig' => $booking_status_orig, 'current_status_orig' => $current_status_orig, 'seat_position' => $seat_position, 'coach_position' => $p['currentCoachId']);
                    }
                    $passengersData = array();
                    /*
                    if ($isconfirmed) {
                        $passengersData = $this->getPassengersDetials(array('pnr' => $params['pnr']));
                        //p($passengersData);
                    }
                    if (empty($passengersData)) {
                        $passengersData = $this->getPNRDetailsByComplainRegister(array('pnr' => $params['pnr']));
                        //print_r($passengers);print_r($passengersData);
                    }
                    if (isset($passengersData['data']['passengers']) && !empty($passengersData['data']['passengers'])) {
                        $passengers = combineArrayValues($passengers, $passengersData['data']['passengers']);
                    }
                    */
                }
                //p($passengers);

                $data['passengers'] = $passengers;
                $data['quota'] = $d['quota'];
                $data['api'] = 'JSON';
                $error = 0;
                $errorcode = '';
                if (!isset($d['trainName'])) {
                    $error = 1;
                    $errorcode = 6;
                    $data = array();
                }
            } else {
                $error = 1;
                $errorcode = 6;
                $data = array();
            }
        } else {
            $error = 1;
            $errorcode = 6;
            $data = array();
        }
        
        if ($this->debug) {
            p($data,false);
        }

        if ($error == 1 && $params['retry'] < $this->retryTimes) {
            $params['retry'] ++;
            usleep(2000);
            return $this->getPNRDataJSON($params);
        } else {
            $ret = array('error' => $error, 'errorcode' => $errorcode, 'data' => $data, 'params' => $params);
            return $ret;
        }
    }

    private function seatPosition($seatno = 0, $class = 'SL', $isGarib = 0) {
        $class = $isGarib ? '3AG' : $class;
        $seatPos = '';
        $classes = array(
            'SL' => array(1 => 'LB', 2 => 'MB', 3 => 'UB', 4 => 'LB', 5 => 'MB', 6 => 'UB', 7 => 'SL', 0 => 'SU'),
            'CC' => array(1 => 'WS', 2 => 'SS', 3 => 'SS', 4 => 'WS', 5 => 'WS', 6 => 'SS', 7 => 'SS', 0 => 'WS'),
            '2S' => array(1 => 'WS', 2 => 'SS', 3 => 'SS', 4 => 'WS', 5 => 'WS', 6 => 'SS', 7 => 'SS', 0 => 'WS'),
            '3A' => array(1 => 'LB', 2 => 'MB', 3 => 'UB', 4 => 'LB', 5 => 'MB', 6 => 'UB', 7 => 'SL', 0 => 'SU'),
            '3AG' => array(1 => 'LB', 2 => 'MB', 3 => 'UB', 4 => 'LB', 5 => 'MB', 6 => 'UB', 7 => 'SL', 8 => 'SM', 0 => 'SU'),
            '2A' => array(1 => 'LB', 2 => 'UB', 3 => 'LB', 4 => 'UB', 5 => 'SL', 0 => 'SU'));
        if ($seatno > 0 && array_key_exists($class, $classes)) {
            $seatPos = $classes[$class][$seatno % count($classes[$class])];
        }
        return $seatPos;
    }


    public function getTrainScheduleDataJSON($params) {
        $ret = array('error' => 1, 'errorcode' => 1, 'data' => array(), 'params' => $params);

        $captchaCode = $this->crackCaptcha();
        if ($captchaCode['error'] == 1) {
            if ($this->debug) {
                echo $captchaCode['errorcode'];
            }
            $captchaCode['errorcode'] = 8;
            return $captchaCode;
        }
        $code = $captchaCode['data']['code'];
        $newheaders = $captchaCode['data']['newheaders'];

        
        usleep(10);
        $p = array();
        $p['url'] = "https://www.indianrail.gov.in/enquiry/CommonCaptcha?inputCaptcha={$code}&trainNo={$params['train_no']}&inputPage=PNR_SCHEDULE_CALL&language=en";
        $p['method'] = 'GET';
        $p['oheaders'] = array(
            "rcheck: 03AF6jDqUw4IxJ3M5ViIX9LH46DczA3RUjhNhinncYQ6yI0ScbfcbmjPE3YTsCaQEj4ucIHBi4EqBBtntoyP6aV6c-tnyAg31rvPjjVRfZkZMkA4W7aAPHIEYbZO5UftjIbgocUmbQzZNi2dXU_iGxyjBjGpK2qj2_h1EwCVUd6wl0IyTOmqNV4NFd4assZava1osHgP7AtT3h49W_WqM0qw6MIPXB3zu7Vey42nP-n0sLMYybPAq4Gdpabcxalac5Du9w9SnYTJdMaMnWHnq6xRSPqx8wjon3v4qZzoo3JkASKc2NFX5iGflIeSD6WOybQ2FYKd9eyGqD",//.generateRandomString(335),
            "user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36",
            "Content-Type: application/json; charset=UTF-8",
            "greq: ".(microtime(true) * 1000)."",
            );
        $p['oheaders'] = array_merge($p['oheaders'], $newheaders);
        $res = makeCURLRequest($p);

        return $res['data'];        
    }


    function RYliveStatus($params = array()) {
        //p($params);
        $ret = array('error' => 1, 'errorcode' => 'Generic error');
        //p($params);
        $trainno = isset($params['trainno']) ? $params['trainno'] : '12391';
        $method = isset($params['method']) ? $params['method'] : 'livestatus';
        $date = isset($params['date']) ? strtotime($params['date']) : time();
        $params['retry'] = isset($params['retry']) ? $params['retry'] : 0;
        $debug = isset($params['debug']) ? $params['debug'] : 0;
        $trainno = str_pad($trainno, 5, "0", STR_PAD_LEFT);

        $date_ = date('Y-m-d', $date);

        $start_day = round((strtotime(date('Y-m-d')) - $date) / (60 * 60 * 24));

        $p = array();
        $p['url'] = "https://livestatus.railyatri.in/api/v3/train_eta_data/{$trainno}/{$date_}.json?no_point=false&train_number={$trainno}&start_day={$start_day}&claim_on_train=FALSE&force_ntes=0&is_location_access_enabled=FALSE&is_gps_enabled=FALSE&user_id=16865647&ontrain=null&operatorName=Bsnl&appid=A4639775D83376A0C6F74713D87FBEE32FA2D67BC77438C0CA901552290C7849&lat=0.0&lng=0.0&accuracy=&time_captured=1442283385468&user_city=&app_res_id=&is_location_access_enabled=false&is_gps_enabled=false&device_type=IOS&device_type_id=2&ecomm_source_id=2&app_id=A4639775D83376A0C6F74713D87FBEE32FA2D67BC77438C0CA901552290C7849&os_v_code=12.1.4&os_v_name=&authentication_token=8a77234998c7e8675a6a2338ee305411&v_code=9&v_num=3.0.7";
        $p['method'] = 'GET';
        $p['timeout'] = rand(1,4);
        //$p['oheaders'] = array('User-agent: Mozilla/5.0 (Linux; Android 8.0; Pixel 2 Build/OPD3.170816.012) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Mobile Safari/537.36');
        $p['oheaders'] = array();
        //p($p);

        $res = makeCURLRequest($p);
        //p($res);

        if ($res['error'] == 1 || empty($res['data'])) {
            if (empty($res['data'])) {
                $res['error'] = 1;
                $res['errorcode'] = 'data empty';
            }
            return $res;
        }
        $arr = json_decode($res['data'], true);
        if ($arr === null && json_last_error() !== JSON_ERROR_NONE) {
            $ret['error'] = 1;
            $ret['errorcode'] = 'Json Encoding failed';
            return $ret;
        }

        if (!isset($arr['success']) || $arr['success'] == false) {
            $ret['errorcode'] = 'no sucess';
            return $ret;
        }

        if (!isset($arr['train_number']) || intval($trainno) != intval($arr['train_number'])) {
            $ret['errorcode'] = 'wrong train no';
            return $ret;
        }

        
        $cancelled = (isset($arr['new_alert_id']) && $arr['new_alert_id'] == 7) ? 1 : 0;
        $cancelled = (isset($arr['new_message']) && stristr($arr['new_message'], ' cancelled')) ? 1 : 0;
        
        $alertMSG = ($cancelled) ? "Train {$trainno} is cancelled for {$date_}" : "";
        $alertMSG = $alertMSG?$alertMSG:((isset($arr['new_message']) && stristr($arr['new_message'], ' rescheduled')) ? "Train {$trainno} is rescheduled for {$date_}. Please call 139 for details."  : '');

        $data = array(
            'yettostart' => $arr['at_src'] ? 1 : 0,
            'currentStation' => isset($arr['current_station_code']) ? $arr['current_station_code'] : (isset($params['stations'][0]['source_code']) ? $params['stations'][0]['source_code'] : ''),
            'currentStationName' => isset($arr['current_station_name']) ? $arr['current_station_name'] : (isset($params['stations'][0]['source_code']) ? $params['stations'][0]['source_code'] : ''),
            'currentStationDeparted' => (isset($arr['status']) && $arr['status'] == 'A') ? 0 : 1,
            'currentStationTime' => isset($arr['etd']) ? $arr['etd'] : 0,
            'currentDistance' => isset($arr['distance_from_source']) ? $arr['distance_from_source'] : 0,
            'currentStationPlatform' => (isset($arr['platform_number']) && $arr['platform_number']>0)?$arr['platform_number']:0,
            'nextStation' => (isset($arr['upcoming_stations'][0]['station_code']) && !empty($arr['upcoming_stations'][0]['station_code'])) ? $arr['upcoming_stations'][0]['station_code'] : (isset($arr['upcoming_stations'][1]['station_code']) ? $arr['upcoming_stations'][1]['station_code'] : ''),
            'nextStationName' => (isset($arr['upcoming_stations'][0]['station_name']) && !empty($arr['upcoming_stations'][0]['station_name'])) ? $arr['upcoming_stations'][0]['station_name'] : (isset($arr['upcoming_stations'][1]['station_name']) ? $arr['upcoming_stations'][1]['station_name'] : ''),
            'nextStationPlatform' => (isset($arr['upcoming_stations'][0]['platform_number']) && !empty($arr['upcoming_stations'][0]['platform_number'])) ? $arr['upcoming_stations'][0]['platform_number'] : (isset($arr['upcoming_stations'][1]['platform_number']) ? $arr['upcoming_stations'][1]['platform_number'] : 0),
            'delayMin' => isset($arr['delay']) ? ($arr['delay']+rand(1,4)) : 0,
            'lastUpdated' => isset($arr['update_time']) ? strtotime($arr['update_time']) - rand(60,130) : time(),
            'terminated' => $arr['at_dstn'] ? 1 : 0,
            'cancelled' => $cancelled,
            'alertMsg' => trim($alertMSG),
            
            'again' => 0,
            'api' => 'RY',
            'url' => $p['url']
        );

        $tmp = array();
        $runnStations = array();

        if (isset($arr['previous_stations']) && is_array($arr['previous_stations'])) {
            $filtered = array_filter($arr['previous_stations'],
            function($v, $k) {
                    return $v['station_code'];
                }, ARRAY_FILTER_USE_BOTH);

                $filtered = array_map(function($arr){
                    return $arr + ['done' => 1];
                }, $filtered);
            $tmp = array_merge($tmp, $filtered);
        }

        if (isset($arr['upcoming_stations']) && is_array($arr['upcoming_stations'])) {
            $filtered = array_filter($arr['upcoming_stations'],
            function($v, $k) {
                    return $v['station_code'];
                }, ARRAY_FILTER_USE_BOTH);

                $filtered = array_map(function($arr){
                    return $arr + ['done' => 0];
                }, $filtered);
            $tmp = array_merge($tmp, $filtered);   
        }
        
        foreach ($tmp as $t) {
            $ea = $t['eta'];
            $ed = $t['etd'];
            $dl = max(0, intval($t['arrival_delay']));
            $dl = $dl>0?($dl+rand(1,4)):$dl;
            $runnStations[$t['station_code']] = array('name'=>$t['station_name'],'ea' => $ea, 'ed' => $ed, 'delay' => $dl,'done'=>$t['done']);
        }
        $data['runnStations'] = $runnStations;
        /*
          $stnFound = false;

          foreach($params['stations']  as $st){
          if($st['source_code'] == $data['currentStation']){
          $stnFound = true;
          break;
          }
          }
          if(!$stnFound && isset($arr['previous_stations'])){
          $stn = end($arr['previous_stations']);
          $stn = isset($stn['non_stops'])?end($stn['non_stops']):$stn;
          $data['currentStation'] = isset($stn['station_code'])?$stn['station_code']:'__NA__';
          $data['currentStationTime'] = isset($stn['std'])?$stn['std']:$data['currentStation'];

          }
         */
        if ($data['terminated']) {
            $data['currentStation'] = (isset($params['stations']) ? end($params['stations'])['source_code'] : '');
        }
        $data['debug'] = $arr;

        $ret['data'] = $data;
        if(!empty($data['runnStations'])){
            $ret['error'] = 0;
            $ret['errorcode'] = '';
        }
        
        return $ret;
    }

    

}

?>