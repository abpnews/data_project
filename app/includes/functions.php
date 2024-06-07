<?php

use Aws\S3\S3Client;

function makeCURLRequest($params = array())
{
    $params['method'] = isset($params['method']) ? $params['method'] : 'GET';
    $params['postvars'] = isset($params['postvars']) ? $params['postvars'] : '';
    $params['timeout'] = isset($params['timeout']) ? $params['timeout'] : 10;
    $params['retry'] = isset($params['retry']) ? $params['retry'] : 1;

    $headers = array(
        "user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36"
    );

    if (isset($params['headers'])) {
        $headers = array_merge($headers, $params['headers']);
    }

    if (isset($params['oheaders'])) {
        $headers = $params['oheaders'];
    }

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $params['url'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => $params['timeout'],
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "{$params['method']}",
        CURLOPT_POSTFIELDS => $params['postvars'],
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HEADER => 1,
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    $errno = curl_errno($curl);
    $header_len = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    //p($err);
    //p($response);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    //p($httpCode);
    curl_close($curl);
    if ($err) {
        if ($errno == 28) {
            $err = 'timeout';
        }
        $return = array('error' => 1, 'errorcode' => $err, 'data' => '', 'headers' => array(), 'reqheaders' => $headers, 'params' => $params);
    } else {
        $error = 0;
        $errorcode = '';

        $respheaders = substr($response, 0, $header_len);
        $response = substr($response, $header_len);

        $respheaders = explode("\r\n", $respheaders);
        $respheaders['httpcode'] = $httpCode;
        if (!($httpCode >= 200 && $httpCode <= 299)) {
            $error = 1;
            $errorcode = $httpCode;
        }
        $sessionheaders = '';
        if (!empty($respheaders)) {
            $newhdrs = array();
            foreach ($respheaders as $h) {
                if (stripos(strtolower($h), 'Set-Cookie:') !== false) {
                    $newhdrs[] = str_replace('Set-Cookie: ', '', current(explode(';', $h)));
                }
            }
            if (!empty($newhdrs)) {
                $sessionheaders = array('Cookie: ' . implode('; ', $newhdrs) . ';');
            }
        }
        $return = array('error' => $error, 'errorcode' => $errorcode, 'data' => $response, 'headers' => $respheaders, 'reqheaders' => $headers, 'params' => $params, 'sessionheaders' => $sessionheaders);
    }
    if ($params['retry'] > 1) {
        $params['retry']--;
        $return = makeCURLRequest($params);
    }
    return $return;
}

function getSlug($str)
{
    $str = strtolower($str);
    $str = preg_replace('/[^a-z0-9 -]+/', '', $str);
    $str = str_replace(' ', '-', $str);
    $str = preg_replace('/\-+/', '-', $str);
    return trim($str, '-');
}

function myUrlEncode($string)
{
    $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
    $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
    return str_replace($entities, $replacements, urlencode($string));
}

function p($var, $exit = true)
{
    print "\n<pre>";
    print_r($var);
    print "</pre>\n";
    if ($exit) {
        exit();
    }
}

function set_page_cache_headers($mins = 10)
{ // Default Cache Time currently 10 mins

    if ($mins == 0) {
        header("Cache-Control: private, no-cache, no-store");
    } else {
        $expires = 60 * $mins;
        header("Pragma: public");
        header("Cache-Control: max-age=" . $expires);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
    }
}


function strToUrl($str)
{
    $str = preg_replace('/\s+/', ' ', trim($str));
    $str = str_replace(' ', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    return strtolower(preg_replace("/[^a-zA-Z0-9-]+/", "", $str));
}


function checkisSet($arr, $key, $default = '')
{
    if (is_array($key)) {
        foreach ($key as $k) {
            if (!isset($arr[$k])) {
                return $default;
            }
            $arr = $arr[$k];
        }
        return $arr;
    }
    return isset($arr[$key]) ? $arr[$key] : $default;
}


function list2s3($param = array())
{
    $bucket = $param['bucket'] ?? '';
    $limit = $param['limit'] ?? 1;
    $delimiter = $param['delimiter'] ?? '';
    $prefix = $delimiter == '/' ? '' : $delimiter;
    $files = array();

    if (APPLICATION_ENV == 'local') {
        $folder = __DIR__ . "/../../local/buckets/{$bucket}/";
        $dir = "{$folder}{$prefix}*";
        //p($folder,false);
        foreach (glob($dir) as $file) {
            $files[] = str_replace("$folder", '', $file) . (intval(is_dir($file)) ? '/' : '');
            if (count($files) >= $limit) {
                break;
            }
        }
    } else {
        try {
            $s3 = new S3Client([
                'version' => 'latest',
                'region'  => REGION,
            ]);

            $result = $s3->listObjectsV2([
                'Bucket' => $bucket, // REQUIRED
                'MaxKeys' => $limit,
                'Delimiter' => $delimiter,
                'Prefix' => $prefix,
                'retries' => [
                    'mode' => 'standard',
                    'max_attempts' => 2,
                ]
            ]);

            if (isset($result['CommonPrefixes']) && is_array($result['CommonPrefixes'])) {
                foreach ($result['CommonPrefixes'] as $c) {
                    $files[] = $c['Prefix'];
                }
            }

            if (isset($result['Contents']) && is_array($result['Contents'])) {
                foreach ($result['Contents'] as $c) {
                    $files[] = $c['Key'];
                }
            }
        } catch (Exception $e) {
            $return = $param;
            $return['error'] = $e->getMessage();
            $B  = BUCKET_JOBS_ERROR;
            $key = "s3Errbucket/" . md5(json_encode($return)) . '.json';
            $prms = array('bucket' => $B, 'key' => $key, 'body' => json_encode($return));
            push2s3($prms);
        }
    }
    return $files;
}


function push2s3($param = array())
{
    $bucket = $param['bucket'] ?? '';
    $key = $param['key'] ?? '';
    $body = $param['body'] ?? '';
    $ACL = $param['ACL'] ?? '';
    $ext = $param['ext'] ?? '';
    //p($body);

    if (APPLICATION_ENV == 'local') {
        $folder = __DIR__ . "/../../local/buckets/";
        $file = $folder . "{$bucket}/{$key}";
        $parts  = explode('/', $file);
        array_pop($parts);
        $dir = implode('/', $parts);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($file, $body);
    } else {

        try {
            $s3 = new S3Client([
                'version' => 'latest',
                'region'  => REGION,
                'retries' => [
                    'mode' => 'standard',
                    'max_attempts' => 2,
                ]
            ]);

            $prm = [
                'Bucket' => $bucket,
                'Key' => $key,
                'Body' => $body,
                'ACL' => $ACL
            ];

            $ctype = getContentType($ext);
            if ($ctype) {
                $prm['ContentType'] = $ctype;
            }

            $s3->putObject($prm);
        } catch (Exception $e) {
            $return = $param;
            $return['error'] = $e->getMessage();
            p($return);
        }
    }
}


function getContentType($ext)
{
    $mimet = array(
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
    );
    return ($mimet[$ext] ?? '');
}

function pull2s3($param = array())
{
    $bucket = $param['bucket'] ?? '';
    $key = $param['key'] ?? '';
    //p($body);
    $result = '';

    if (APPLICATION_ENV == 'local') {
        $folder = __DIR__ . "/../../local/buckets/";
        $result = @file_get_contents($folder . "{$bucket}/{$key}");
    } else {

        try {
            $s3 = new S3Client([
                'version' => 'latest',
                'region'  => REGION,
                'retries' => [
                    'mode' => 'standard',
                    'max_attempts' => 2,
                ]
            ]);

            $result = $s3->getObject([
                'Bucket' => $bucket,
                'Key' => $key
            ]);
            $result = $result['Body']->__toString();
        } catch (Exception $e) {
            /*
            $return = $param;
            $return['error'] = $e->getMessage();
            $B  = BUCKET_JOBS_ERROR;
            $key = "s3bucket/" . md5(json_encode($return)).'.json';
            $prms = array('bucket' => $B, 'key' => $key, 'body' => json_encode($return));
            push2s3($prms);
            */
        }
    }

    return $result;
}

function remove2s3($param = array())
{
    $bucket = $param['bucket'] ?? '';
    $key = $param['key'] ?? '';
    //p($body);
    $result = '';

    if (APPLICATION_ENV == 'local') {
        $folder = __DIR__ . "/../../local/buckets/";
        $result = unlink($folder . "{$bucket}/{$key}");
    } else {

        try {
            $s3 = new S3Client([
                'version' => 'latest',
                'region'  => REGION,
                'retries' => [
                    'mode' => 'standard',
                    'max_attempts' => 2,
                ]
            ]);

            $result = $s3->deleteObject([
                'Bucket' => $bucket,
                'Key' => $key
            ]);
        } catch (Exception $e) {
            $return = $param;
            $return['error'] = $e->getMessage();
            $B  = BUCKET_JOBS_ERROR;
            $key = "s3Errbucket/" . md5(json_encode($return)) . '.json';
            $prms = array('bucket' => $B, 'key' => $key, 'body' => json_encode($return));
            push2s3($prms);
        }
    }

    return $result;
}

function pullAnys3($param = array())
{
    $bucket = $param['bucket'] ?? '';
    $key = '';
    //p($body);

    if (APPLICATION_ENV == 'local') {
        $folder = __DIR__ . "/../../local/buckets/";
        $files = scandir($folder . "{$bucket}/");
        $key = isset($files[2]) ? $files[2] : '';
    } else {

        try {
            $s3 = new S3Client([
                'version' => 'latest',
                'region'  => REGION,
            ]);

            $iterator = $s3->getIterator('ListObjects', array(
                "Bucket" => $bucket
            ), array(
                'limit'  => 20,
            ));

            $keys = array();
            foreach ($iterator as $object) {
                $keys[] = $object['Key'];
            }

            if (count($keys) > 0) {
                $k = array_rand($keys);
                $key = $keys[$k];
            }
        } catch (Exception $e) {
            $return = $param;
            $return['error'] = $e->getMessage();
            $B  = BUCKET_JOBS_ERROR;
            $key = "s3Errbucket/" . md5(json_encode($return)) . '.json';
            $prms = array('bucket' => $B, 'key' => $key, 'body' => json_encode($return));
            push2s3($prms);
        }
    }

    return $key;
}

function writeFile($content, $filename)
{
    $folder = "/tmp/";
    $fileLocation = $folder . $filename . ".json";
    $file = fopen($fileLocation, "w");
    fwrite($file, $content);
    fclose($file);
}

function validateEmailDomain($email, $domains)
{
    foreach ($domains as $domain) {
        $pos = strpos($email, $domain, strlen($email) - strlen($domain));

        if ($pos === false)
            continue;

        if ($pos == 0 || $email[(int) $pos - 1] == "@" || $email[(int) $pos - 1] == ".")
            return true;
    }

    return false;
}

function getCityforFuel(){
    $city = '{"TG":[{"cityName":"ADILABAD","code":"TG001"},{"cityName":"BHADRADRI KOTHAGUDEM","code":"TG020"},{"cityName":"HYDERABAD","code":"TG010"},{"cityName":"JAGITIAL","code":"TG013"},{"cityName":"JANGAON","code":"TG018"},{"cityName":"JAYASHANKAR BHUPALPA","code":"TG031"},{"cityName":"JOGULAMBA GADWAL","code":"TG025"},{"cityName":"KAMAREDDY","code":"TG016"},{"cityName":"KARIM NAGAR","code":"TG002"},{"cityName":"KHAMMAM","code":"TG003"},{"cityName":"KOMRAM BHEEM ASIFABA","code":"TG012"},{"cityName":"MAHABUBABAD","code":"TG019"},{"cityName":"MANCHERIAL","code":"TG030"},{"cityName":"MEDAK","code":"TG004"},{"cityName":"MEDCHAL MALKAJGIRI","code":"TG029"},{"cityName":"MEHABUBNAGAR","code":"TG005"},{"cityName":"MULUGU","code":"TG032"},{"cityName":"NAGARKURNOOL","code":"TG024"},{"cityName":"NALGONDA","code":"TG006"},{"cityName":"NARAYANPET","code":"TG033"},{"cityName":"NIRMAL","code":"TG011"},{"cityName":"NIZAMABAD","code":"TG007"},{"cityName":"PEDDAPALLI","code":"TG014"},{"cityName":"RAJANNA SIRCILLA","code":"TG015"},{"cityName":"RANGAREDDI","code":"TG008"},{"cityName":"SANGAREDDY","code":"TG021"},{"cityName":"SIDDIPET","code":"TG022"},{"cityName":"SURYAPET","code":"TG026"},{"cityName":"VIKARABAD","code":"TG028"},{"cityName":"WANAPARTHY","code":"TG023"},{"cityName":"WARANGAL","code":"TG009"},{"cityName":"WARANGAL RURAL","code":"TG017"},{"cityName":"YADADRI BHUVANAGIRI","code":"TG027"}],"MP":[{"cityName":"AGAR MALWA","code":"MP051"},{"cityName":"ALIRAJPUR","code":"MP050"},{"cityName":"ANUPUR","code":"MP047"},{"cityName":"ASHOKNAGAR","code":"MP048"},{"cityName":"BADWANI","code":"MP001"},{"cityName":"BALAGHAT","code":"MP002"},{"cityName":"BETUL","code":"MP003"},{"cityName":"BHIND","code":"MP004"},{"cityName":"BHOPAL","code":"MP005"},{"cityName":"BURHANPUR","code":"MP046"},{"cityName":"CHHATARPUR","code":"MP006"},{"cityName":"CHHINDWARE","code":"MP007"},{"cityName":"DAMOH","code":"MP008"},{"cityName":"DATIA","code":"MP009"},{"cityName":"DEWAS","code":"MP010"},{"cityName":"DHAR","code":"MP011"},{"cityName":"DINDORI","code":"MP012"},{"cityName":"GUNA","code":"MP014"},{"cityName":"GWALIOR","code":"MP015"},{"cityName":"HARDA","code":"MP016"},{"cityName":"HOSHANGABAD","code":"MP017"},{"cityName":"INDORE","code":"MP018"},{"cityName":"JABALPUR","code":"MP019"},{"cityName":"JAHBUA","code":"MP020"},{"cityName":"KATNI","code":"MP021"},{"cityName":"KHANDWA","code":"MP013"},{"cityName":"KHARGONE","code":"MP045"},{"cityName":"MANDLA","code":"MP022"},{"cityName":"MANDSAUR","code":"MP023"},{"cityName":"MORENA","code":"MP024"},{"cityName":"NARSIMHAPUR","code":"MP025"},{"cityName":"NEEMACH","code":"MP026"},{"cityName":"PANNA","code":"MP027"},{"cityName":"RAISEN","code":"MP028"},{"cityName":"RAJGARH","code":"MP029"},{"cityName":"RATLAM","code":"MP030"},{"cityName":"REWA","code":"MP031"},{"cityName":"SAGAR","code":"MP032"},{"cityName":"SATNA","code":"MP033"},{"cityName":"SEHORE","code":"MP034"},{"cityName":"SEONI","code":"MP035"},{"cityName":"SHAHDOL","code":"MP036"},{"cityName":"SHAJAPUR","code":"MP037"},{"cityName":"SHEOPUR","code":"MP038"},{"cityName":"SHIVPURI","code":"MP039"},{"cityName":"SIDHI","code":"MP040"},{"cityName":"SINGRAULI","code":"MP049"},{"cityName":"TIKAMGARH","code":"MP041"},{"cityName":"UJJAIN","code":"MP042"},{"cityName":"UMARIA","code":"MP043"},{"cityName":"VIDISHA","code":"MP044"}],"UP":[{"cityName":"AGRA","code":"UP001"},{"cityName":"ALIGARH","code":"UP002"},{"cityName":"ALLAHABAD","code":"UP003"},{"cityName":"AMBEDKARNAGAR","code":"UP004"},{"cityName":"AMETHI\/CSM NAGAR","code":"UP074"},{"cityName":"AMROHA","code":"UP038"},{"cityName":"AURAIYA","code":"UP005"},{"cityName":"AZAMGARH","code":"UP006"},{"cityName":"BAGHPAT","code":"UP007"},{"cityName":"BAHRAICH","code":"UP008"},{"cityName":"BALLIA","code":"UP009"},{"cityName":"BALRAMPUR","code":"UP010"},{"cityName":"BANDA","code":"UP011"},{"cityName":"BARABANKI","code":"UP012"},{"cityName":"BAREILLY","code":"UP013"},{"cityName":"BASTI","code":"UP014"},{"cityName":"BIJNOR","code":"UP015"},{"cityName":"BUDAUN","code":"UP016"},{"cityName":"BULANDSHAHR","code":"UP017"},{"cityName":"CHANDAULI","code":"UP018"},{"cityName":"CHITRAKUT","code":"UP019"},{"cityName":"DEORIA","code":"UP020"},{"cityName":"ETAH","code":"UP021"},{"cityName":"ETAWAH","code":"UP022"},{"cityName":"FAIZABAD","code":"UP023"},{"cityName":"FARRUKKHABAD","code":"UP024"},{"cityName":"FATEHPUR","code":"UP025"},{"cityName":"FIROZABAD","code":"UP026"},{"cityName":"GAUTAM BUDH NAGAR","code":"UP027"},{"cityName":"GHAZIABAD","code":"UP028"},{"cityName":"GHAZIPUR","code":"UP029"},{"cityName":"GONDA","code":"UP030"},{"cityName":"GORAKHPUR","code":"UP031"},{"cityName":"HAMIRPUR","code":"UP032"},{"cityName":"HAPUR","code":"UP077"},{"cityName":"HARDOI","code":"UP033"},{"cityName":"HATHRAS","code":"UP034"},{"cityName":"JALAUN","code":"UP035"},{"cityName":"JAUNPUR","code":"UP036"},{"cityName":"JHANSI","code":"UP037"},{"cityName":"KANNUAJ","code":"UP039"},{"cityName":"KANPUR RURAL","code":"UP040"},{"cityName":"KANPUR URBAN","code":"UP041"},{"cityName":"KASHI RAM NAGAR","code":"UP073"},{"cityName":"KAUSHAMBI","code":"UP042"},{"cityName":"KUSHINAGAR","code":"UP043"},{"cityName":"LAKHIMPUR","code":"UP044"},{"cityName":"LALITPUR","code":"UP045"},{"cityName":"LUCKNOW","code":"UP046"},{"cityName":"MAHARAJGANJ","code":"UP047"},{"cityName":"MAHOBA","code":"UP048"},{"cityName":"MAINPURI","code":"UP049"},{"cityName":"MATHURA","code":"UP050"},{"cityName":"MAUNATHBHANJAN","code":"UP051"},{"cityName":"MEERUT","code":"UP052"},{"cityName":"MIRZAPUR","code":"UP053"},{"cityName":"MORADABAD","code":"UP054"},{"cityName":"MUZAFFARNAGAR","code":"UP055"},{"cityName":"PILIBHIT","code":"UP056"},{"cityName":"PRATAPGARH","code":"UP057"},{"cityName":"RAE BARELI","code":"UP058"},{"cityName":"RAMPUR","code":"UP059"},{"cityName":"SAHARANPUR","code":"UP060"},{"cityName":"SAMBHAL","code":"UP075"},{"cityName":"SANT KABIR NAGAR","code":"UP061"},{"cityName":"SANT RAVI NAGAR","code":"UP062"},{"cityName":"SHAHJAHANPUR","code":"UP063"},{"cityName":"SHAMLI","code":"UP076"},{"cityName":"SHRAVASTI","code":"UP064"},{"cityName":"SIDHARTHNAGAR","code":"UP065"},{"cityName":"SITAPUR","code":"UP066"},{"cityName":"SONBHADRA","code":"UP067"},{"cityName":"SULTANPUR","code":"UP068"},{"cityName":"UNNAO","code":"UP069"},{"cityName":"VARANASI","code":"UP070"}],"MH":[{"cityName":"AHMADNAGAR","code":"MH001"},{"cityName":"AKOLA","code":"MH002"},{"cityName":"AMRAVATI","code":"MH003"},{"cityName":"AURANGABAD","code":"MH004"},{"cityName":"BHANDARA","code":"MH005"},{"cityName":"BID","code":"MH006"},{"cityName":"BULDHANA","code":"MH007"},{"cityName":"CHANDRAPUR","code":"MH008"},{"cityName":"DHULE","code":"MH010"},{"cityName":"GADCHIROLI","code":"MH011"},{"cityName":"GONDIA","code":"MH033"},{"cityName":"GREATER MUMBAI","code":"MH012"},{"cityName":"HINGOLI","code":"MH034"},{"cityName":"JALGAON","code":"MH013"},{"cityName":"JALNA","code":"MH014"},{"cityName":"KOLHAPUR","code":"MH015"},{"cityName":"LATUR","code":"MH016"},{"cityName":"MUMBAI CITY","code":"MH036"},{"cityName":"NAGPUR","code":"MH017"},{"cityName":"NANDED","code":"MH018"},{"cityName":"NANDURBAR","code":"MH019"},{"cityName":"NASHIK","code":"MH020"},{"cityName":"OSMANABAD","code":"MH009"},{"cityName":"PALGHAR","code":"MH035"},{"cityName":"PARBHANI","code":"MH021"},{"cityName":"PUNE","code":"MH022"},{"cityName":"RAIGARH","code":"MH023"},{"cityName":"RATNAGIRI","code":"MH024"},{"cityName":"SANGLI","code":"MH025"},{"cityName":"SATARA","code":"MH026"},{"cityName":"SINDHUDURG","code":"MH027"},{"cityName":"SOLAPUR","code":"MH028"},{"cityName":"THANE","code":"MH029"},{"cityName":"WARDHA","code":"MH030"},{"cityName":"WASHIM","code":"MH031"},{"cityName":"YAVATMAL","code":"MH032"}],"GJ":[{"cityName":"AHMEDABAD","code":"GJ001"},{"cityName":"AMRELI","code":"GJ002"},{"cityName":"ANAND","code":"GJ003"},{"cityName":"ARAVALLI","code":"GJ027"},{"cityName":"BANAS KANTHA","code":"GJ004"},{"cityName":"BHARUCH","code":"GJ005"},{"cityName":"BHAVNAGAR","code":"GJ006"},{"cityName":"BOTAD","code":"GJ029"},{"cityName":"CHHOTAUDEPUR","code":"GJ030"},{"cityName":"DAHOD","code":"GJ007"},{"cityName":"DEVBHUMI DWARKA","code":"GJ033"},{"cityName":"GANDHI NAGAR","code":"GJ008"},{"cityName":"GIR SOMNATH","code":"GJ028"},{"cityName":"JAMNAGAR","code":"GJ009"},{"cityName":"JUNAGADH","code":"GJ010"},{"cityName":"KHEDA","code":"GJ011"},{"cityName":"KUTCH","code":"GJ012"},{"cityName":"MAHISAGAR","code":"GJ031"},{"cityName":"MEHSANA","code":"GJ013"},{"cityName":"MORBI","code":"GJ032"},{"cityName":"NARMADA","code":"GJ014"},{"cityName":"NAVSARI","code":"GJ015"},{"cityName":"PANCH MAHAL","code":"GJ016"},{"cityName":"PATAN","code":"GJ017"},{"cityName":"PORBANDER","code":"GJ018"},{"cityName":"RAJKOT","code":"GJ019"},{"cityName":"SABAR KANTHA","code":"GJ020"},{"cityName":"SURAT","code":"GJ021"},{"cityName":"SURENDRANAGAR","code":"GJ022"},{"cityName":"TAPI","code":"GJ026"},{"cityName":"THE DANGS","code":"GJ023"},{"cityName":"VADODARA","code":"GJ024"},{"cityName":"VALSAD","code":"GJ025"}],"MZ":[{"cityName":"AIZAWL","code":"MZ001"},{"cityName":"CHAMPHAI","code":"MZ007"},{"cityName":"KOLASIB","code":"MZ006"},{"cityName":"LAWNGTLAI","code":"MZ004"},{"cityName":"LUNGLEI","code":"MZ003"},{"cityName":"MAMIT","code":"MZ005"},{"cityName":"SAIHA","code":"MZ002"},{"cityName":"SERCHHIP","code":"MZ008"}],"RJ":[{"cityName":"AJMER","code":"RJ001"},{"cityName":"ALWAR","code":"RJ002"},{"cityName":"BANSWARA","code":"RJ003"},{"cityName":"BARAN","code":"RJ004"},{"cityName":"BARMER","code":"RJ005"},{"cityName":"BHARATPUR","code":"RJ006"},{"cityName":"BHILWARA","code":"RJ007"},{"cityName":"BIKANER","code":"RJ008"},{"cityName":"BUNDI","code":"RJ009"},{"cityName":"CHITTAURGARH","code":"RJ010"},{"cityName":"CHURU","code":"RJ011"},{"cityName":"DAUSA","code":"RJ012"},{"cityName":"DHAULPUR","code":"RJ013"},{"cityName":"DUNGARPUR","code":"RJ014"},{"cityName":"GANGANAGAR","code":"RJ015"},{"cityName":"HANUMANGARH","code":"RJ016"},{"cityName":"JAIPUR","code":"RJ017"},{"cityName":"JAISALMER","code":"RJ018"},{"cityName":"JALOR","code":"RJ019"},{"cityName":"JHALAWAR","code":"RJ020"},{"cityName":"JHUNJHUNUN","code":"RJ021"},{"cityName":"JODHPUR","code":"RJ022"},{"cityName":"KARAULI","code":"RJ023"},{"cityName":"KOTA","code":"RJ024"},{"cityName":"NAGAUR","code":"RJ025"},{"cityName":"PALI","code":"RJ026"},{"cityName":"PRATAPGARH","code":"RJ033"},{"cityName":"RAJSAMAND","code":"RJ027"},{"cityName":"SAWAIMADHOPUR","code":"RJ028"},{"cityName":"SIKAR","code":"RJ029"},{"cityName":"SIROHI","code":"RJ030"},{"cityName":"TONK","code":"RJ031"},{"cityName":"UDAIPUR","code":"RJ032"}],"KL":[{"cityName":"ALAPPUZHA","code":"KL001"},{"cityName":"ERNAKULAM","code":"KL002"},{"cityName":"IDUKKI","code":"KL003"},{"cityName":"KANNUR","code":"KL004"},{"cityName":"KASARAGOD","code":"KL005"},{"cityName":"KOLLAM","code":"KL006"},{"cityName":"KOTTAYAM","code":"KL007"},{"cityName":"KOZHIKODE","code":"KL008"},{"cityName":"MALAPPURAM","code":"KL009"},{"cityName":"PALAKKAD","code":"KL010"},{"cityName":"PATHANANTHITTA","code":"KL011"},{"cityName":"THIRUVANANTHAPURAM","code":"KL012"},{"cityName":"THRISSUR","code":"KL013"},{"cityName":"WAYANAD","code":"KL014"}],"WB":[{"cityName":"ALIPURDUAR","code":"WB020"},{"cityName":"BANKURA","code":"WB001"},{"cityName":"BIRBHUM","code":"WB003"},{"cityName":"COOCH BIHAR","code":"WB005"},{"cityName":"DAKSHIN DINAJPUR","code":"WB006"},{"cityName":"DARJEELING","code":"WB007"},{"cityName":"HOOGHLY","code":"WB008"},{"cityName":"HOWRAH","code":"WB009"},{"cityName":"JALPAIGURI","code":"WB010"},{"cityName":"JHARGRAM","code":"WB024"},{"cityName":"KALIMPONG","code":"WB021"},{"cityName":"KOLKATA","code":"WB004"},{"cityName":"MALDA","code":"WB011"},{"cityName":"MURSHIDABAD","code":"WB013"},{"cityName":"NADIA","code":"WB014"},{"cityName":"NORTH 24 PARGANAS","code":"WB015"},{"cityName":"PASCHIM BARDHAMAN","code":"WB023"},{"cityName":"PASCHIM MEDINIPUR","code":"WB019"},{"cityName":"PURBA BARDHAMAN","code":"WB022"},{"cityName":"PURBA MEDINIPUR","code":"WB012"},{"cityName":"PURULIA","code":"WB016"},{"cityName":"SOUTH 24 PARGANAS","code":"WB017"},{"cityName":"UTTAR DINAJPUR","code":"WB018"}],"UA":[{"cityName":"ALMORA","code":"UA001"},{"cityName":"BAGESHWAR","code":"UA002"},{"cityName":"CHAMOLI","code":"UA003"},{"cityName":"CHAMPAWAT","code":"UA004"},{"cityName":"DEHRADUN","code":"UA005"},{"cityName":"HARIDWAR","code":"UA006"},{"cityName":"NAINITAL","code":"UA007"},{"cityName":"PAURI","code":"UA008"},{"cityName":"PITHORAGARH","code":"UA009"},{"cityName":"RUDRAPRAYAG","code":"UA010"},{"cityName":"TEHRI GARHWAL","code":"UA011"},{"cityName":"UDHAM SINGH NAGAR","code":"UA012"},{"cityName":"UTTARKASHI","code":"UA013"}],"HR":[{"cityName":"AMBALA","code":"HR001"},{"cityName":"BHIWANI","code":"HR006"},{"cityName":"CHARKI DADRI","code":"HR022"},{"cityName":"FARIDABAD","code":"HR016"},{"cityName":"FATEHABAD","code":"HR011"},{"cityName":"GURGAON","code":"HR002"},{"cityName":"HISAR","code":"HR007"},{"cityName":"JHAJJAR","code":"HR012"},{"cityName":"JIND","code":"HR017"},{"cityName":"KAITHAL","code":"HR003"},{"cityName":"KARNAL","code":"HR008"},{"cityName":"KURUKSHETRA","code":"HR013"},{"cityName":"MAHENDRAGARH","code":"HR018"},{"cityName":"MEWAT","code":"HR021"},{"cityName":"PALWAL","code":"HR020"},{"cityName":"PANCHKULA","code":"HR004"},{"cityName":"PANIPAT","code":"HR009"},{"cityName":"REWARI","code":"HR014"},{"cityName":"ROHTAK","code":"HR019"},{"cityName":"SIRSA","code":"HR005"},{"cityName":"SONIPAT","code":"HR010"},{"cityName":"YAMUNANAGAR","code":"HR015"}],"PB":[{"cityName":"AMRITSAR","code":"PB001"},{"cityName":"BARNALA","code":"PB020"},{"cityName":"BATHINDA","code":"PB002"},{"cityName":"FARIDKOT","code":"PB003"},{"cityName":"FATEHGARH SAHIB","code":"PB004"},{"cityName":"FAZILKA","code":"PB022"},{"cityName":"FIROZPUR","code":"PB005"},{"cityName":"GURDASPUR","code":"PB006"},{"cityName":"HOSHIARPUR","code":"PB007"},{"cityName":"JALANDHAR","code":"PB008"},{"cityName":"KAPURTHALA","code":"PB009"},{"cityName":"LUDHIANA","code":"PB010"},{"cityName":"MANSA","code":"PB011"},{"cityName":"MOGA","code":"PB012"},{"cityName":"MUKTSAR","code":"PB013"},{"cityName":"PATHANKOT","code":"PB021"},{"cityName":"PATIALA","code":"PB015"},{"cityName":"RUPNAGAR","code":"PB016"},{"cityName":"SANGRUR","code":"PB017"},{"cityName":"SAS NAGAR","code":"PB019"},{"cityName":"SHD BHAGAT SINGH NGR","code":"PB014"},{"cityName":"TARN TARAN","code":"PB018"}],"AP":[{"cityName":"ANANTAPUR","code":"AP002"},{"cityName":"CHITTOOR","code":"AP003"},{"cityName":"CUDDAPAH","code":"AP004"},{"cityName":"EAST GODAVARI","code":"AP005"},{"cityName":"GUNTUR","code":"AP006"},{"cityName":"KRISHNA","code":"AP009"},{"cityName":"KURNOOL","code":"AP010"},{"cityName":"NELLORE","code":"AP014"},{"cityName":"PRAKASAM","code":"AP016"},{"cityName":"SRIKAKULAM","code":"AP018"},{"cityName":"VISHAKHAPATNAM","code":"AP019"},{"cityName":"VIZIANAGARAM","code":"AP020"},{"cityName":"WEST GODAVARI","code":"AP022"}],"JK":[{"cityName":"ANANTNAG","code":"JK001"},{"cityName":"BADGAM","code":"JK004"},{"cityName":"BANDIPORA","code":"JK020"},{"cityName":"BARAMULLAH","code":"JK003"},{"cityName":"DODA","code":"JK012"},{"cityName":"GANDERBAL","code":"JK022"},{"cityName":"JAMMU","code":"JK005"},{"cityName":"KATHUA","code":"JK007"},{"cityName":"KISHTWAR","code":"JK016"},{"cityName":"KULGAM","code":"JK021"},{"cityName":"KUPWARA","code":"JK010"},{"cityName":"POONCH","code":"JK011"},{"cityName":"PULWAMA","code":"JK002"},{"cityName":"RAJOURI","code":"JK013"},{"cityName":"RAMBAN","code":"JK019"},{"cityName":"REASI","code":"JK018"},{"cityName":"SAMBA","code":"JK017"},{"cityName":"SHOPIAN","code":"JK015"},{"cityName":"SRINAGAR","code":"JK006"},{"cityName":"UDHAMPUR","code":"JK014"}],"OR":[{"cityName":"ANGUL","code":"OR001"},{"cityName":"BALESHWAR","code":"OR003"},{"cityName":"BARGARH","code":"OR004"},{"cityName":"BHADRAK","code":"OR006"},{"cityName":"BOLANGIR","code":"OR002"},{"cityName":"BOUDH","code":"OR005"},{"cityName":"CUTTACK","code":"OR007"},{"cityName":"DEOGARH","code":"OR008"},{"cityName":"DHENKANAL","code":"OR009"},{"cityName":"GAJAPATI","code":"OR010"},{"cityName":"GANJAM","code":"OR011"},{"cityName":"JAGATSINGHPUR","code":"OR012"},{"cityName":"JAJPUR","code":"OR013"},{"cityName":"JHARSUGUDA","code":"OR014"},{"cityName":"KALAHANDI","code":"OR015"},{"cityName":"KANDHAMAL","code":"OR025"},{"cityName":"KENDRAPARA","code":"OR016"},{"cityName":"KEONJHAR","code":"OR017"},{"cityName":"KHORDHA","code":"OR018"},{"cityName":"KORAPUT","code":"OR019"},{"cityName":"MALKANGIRI","code":"OR020"},{"cityName":"MAYURBHANJ","code":"OR021"},{"cityName":"NABARANGAPUR","code":"OR022"},{"cityName":"NAYAGARH","code":"OR023"},{"cityName":"NUAPARHA","code":"OR024"},{"cityName":"PURI","code":"OR026"},{"cityName":"RAYAGADA","code":"OR027"},{"cityName":"SAMBALPUR","code":"OR028"},{"cityName":"SONAPUR","code":"OR029"},{"cityName":"SUNDARGARH","code":"OR030"}],"BR":[{"cityName":"ARARIA","code":"BR001"},{"cityName":"ARWAL","code":"BR039"},{"cityName":"AURANGABAD","code":"BR002"},{"cityName":"BANKA","code":"BR003"},{"cityName":"BEGUSARAI","code":"BR004"},{"cityName":"BHAGALPUR","code":"BR006"},{"cityName":"BHOJPUR","code":"BR007"},{"cityName":"BUXAR","code":"BR008"},{"cityName":"DARBHANGA","code":"BR009"},{"cityName":"EAST CHAMPARAN","code":"BR026"},{"cityName":"GAYA","code":"BR010"},{"cityName":"GOPALGANJ","code":"BR011"},{"cityName":"JAHANABAD","code":"BR012"},{"cityName":"JAMUI","code":"BR013"},{"cityName":"KAIMUR","code":"BR005"},{"cityName":"KATIHAR","code":"BR014"},{"cityName":"KHAGARIA","code":"BR015"},{"cityName":"KISHANGANJ","code":"BR016"},{"cityName":"LUCKEESARAI","code":"BR017"},{"cityName":"MADHEPURA","code":"BR018"},{"cityName":"MADHUBANI","code":"BR019"},{"cityName":"MUNGER","code":"BR020"},{"cityName":"MUZAFFARPUR","code":"BR021"},{"cityName":"NALANDA","code":"BR022"},{"cityName":"NAWADA","code":"BR023"},{"cityName":"PATNA","code":"BR025"},{"cityName":"PURNIA","code":"BR027"},{"cityName":"ROHTAS","code":"BR028"},{"cityName":"SAHARSA","code":"BR029"},{"cityName":"SAMASTIPUR","code":"BR030"},{"cityName":"SARAN","code":"BR031"},{"cityName":"SEWAN","code":"BR032"},{"cityName":"SHEIKHPURA","code":"BR033"},{"cityName":"SHEOHAR","code":"BR034"},{"cityName":"SITAMARHI","code":"BR035"},{"cityName":"SUPAUL","code":"BR036"},{"cityName":"VAISHALI","code":"BR037"},{"cityName":"WEST CHAMPARAN","code":"BR024"}],"TN":[{"cityName":"ARIYALUR","code":"TN041"},{"cityName":"CHENNAI","code":"TN001"},{"cityName":"COIMBATORE","code":"TN002"},{"cityName":"CUDDALORE","code":"TN003"},{"cityName":"DHARMAPURI","code":"TN004"},{"cityName":"DINDIGUL","code":"TN005"},{"cityName":"ERODE","code":"TN006"},{"cityName":"KANCHIPURAM","code":"TN007"},{"cityName":"KANNIYAKUMARI","code":"TN008"},{"cityName":"KARUR","code":"TN009"},{"cityName":"KRISHNAGIRI","code":"TN033"},{"cityName":"MADURAI","code":"TN010"},{"cityName":"NAGAPATTINAM","code":"TN011"},{"cityName":"NAMAKKAL","code":"TN012"},{"cityName":"NILGIRIS","code":"TN013"},{"cityName":"PERAMBALUR","code":"TN014"},{"cityName":"PUDUKKOTTAI","code":"TN015"},{"cityName":"RAMANATHAPURAM","code":"TN016"},{"cityName":"SALEM","code":"TN017"},{"cityName":"SIVAGANGA","code":"TN018"},{"cityName":"TENI","code":"TN019"},{"cityName":"THANJAVUR","code":"TN020"},{"cityName":"THIRUVARUR","code":"TN021"},{"cityName":"TIRUCHCHIRAPPALLI","code":"TN022"},{"cityName":"TIRUNELVELI","code":"TN023"},{"cityName":"TIRUPUR","code":"TN040"},{"cityName":"TIRUVALLUR","code":"TN024"},{"cityName":"TIRUVANNAMALAI","code":"TN025"},{"cityName":"TUTICORIN","code":"TN026"},{"cityName":"VELLORE","code":"TN027"},{"cityName":"VILUPPURAM","code":"TN028"},{"cityName":"VIRUDUNAGAR","code":"TN029"}],"KA":[{"cityName":"BAGALKOT","code":"KA001"},{"cityName":"BANGALORE","code":"KA002"},{"cityName":"BANGALORE RURAL","code":"KA027"},{"cityName":"BELGAUM","code":"KA003"},{"cityName":"BELLARY","code":"KA004"},{"cityName":"BIDAR","code":"KA005"},{"cityName":"BIJAPUR","code":"KA006"},{"cityName":"CHAMRAJNAGAR","code":"KA007"},{"cityName":"CHIKKABALLAPURA","code":"KA028"},{"cityName":"CHIKMAGALUR","code":"KA008"},{"cityName":"CHITRADURGA","code":"KA009"},{"cityName":"DAKSHIN KANNAD","code":"KA010"},{"cityName":"DAVANGERE","code":"KA011"},{"cityName":"DHARWAD","code":"KA012"},{"cityName":"GADAG","code":"KA013"},{"cityName":"GULBARGA","code":"KA014"},{"cityName":"HASSAN","code":"KA015"},{"cityName":"HAVERI","code":"KA016"},{"cityName":"KODAGU","code":"KA017"},{"cityName":"KOLAR","code":"KA018"},{"cityName":"KOPPAL","code":"KA019"},{"cityName":"MANDYA","code":"KA020"},{"cityName":"MYSORE","code":"KA021"},{"cityName":"RAICHUR","code":"KA022"},{"cityName":"RAMANAGARA","code":"KA029"},{"cityName":"SHIMOGA","code":"KA023"},{"cityName":"TUMKUR","code":"KA024"},{"cityName":"UDUPI","code":"KA025"},{"cityName":"UTTAR KANNAD","code":"KA026"},{"cityName":"YADGIR","code":"KA030"}],"AS":[{"cityName":"BAKSA","code":"AS026"},{"cityName":"BARPETA","code":"AS001"},{"cityName":"BONGAIGAON","code":"AS024"},{"cityName":"Biswanath","code":"AS032"},{"cityName":"CACHAR","code":"AS003"},{"cityName":"CHIRANG","code":"AS002"},{"cityName":"Charaideo","code":"AS030"},{"cityName":"DARRANG","code":"AS004"},{"cityName":"DHEMAJI","code":"AS005"},{"cityName":"DHUBURI","code":"AS006"},{"cityName":"DIBRUGARH","code":"AS007"},{"cityName":"DIMA HASAO","code":"AS020"},{"cityName":"GOALPARA","code":"AS008"},{"cityName":"GOLAGHAT","code":"AS009"},{"cityName":"HAILAKANDI","code":"AS010"},{"cityName":"Hojai","code":"AS028"},{"cityName":"JORHAT","code":"AS011"},{"cityName":"KAMRUP","code":"AS012"},{"cityName":"KARBI ANGLONG","code":"AS013"},{"cityName":"KARIMGANJ","code":"AS014"},{"cityName":"KOKRAJHAR","code":"AS015"},{"cityName":"Kamrup Metro","code":"AS027"},{"cityName":"LAKHIMPUR","code":"AS016"},{"cityName":"MAJULI","code":"AS035"},{"cityName":"MORIGAON","code":"AS017"},{"cityName":"NAGAON","code":"AS018"},{"cityName":"NALBARI","code":"AS019"},{"cityName":"SIBSAGAR","code":"AS021"},{"cityName":"SONITPUR","code":"AS022"},{"cityName":"TINSUKIA","code":"AS023"},{"cityName":"UDALGURI","code":"AS025"},{"cityName":"West Karbi Anglong","code":"AS029"}],"CT":[{"cityName":"BALOD","code":"CT024"},{"cityName":"BALODABAZAR","code":"CT021"},{"cityName":"BALRAMPUR","code":"CT027"},{"cityName":"BASTAR","code":"CT001"},{"cityName":"BEMETARA","code":"CT023"},{"cityName":"BIJAPUR","code":"CT017"},{"cityName":"BILASPUR","code":"CT002"},{"cityName":"DANTEWADA","code":"CT003"},{"cityName":"DHAMTARI","code":"CT004"},{"cityName":"DURG","code":"CT005"},{"cityName":"GARIYABAND","code":"CT022"},{"cityName":"JANJGIR","code":"CT006"},{"cityName":"JASHPUR","code":"CT007"},{"cityName":"KANKER","code":"CT008"},{"cityName":"KAWARDHA","code":"CT009"},{"cityName":"KONDAGAON","code":"CT020"},{"cityName":"KORBA","code":"CT010"},{"cityName":"KORIA","code":"CT011"},{"cityName":"MAHASAMUND","code":"CT012"},{"cityName":"MUNGELI","code":"CT025"},{"cityName":"RAIGARH","code":"CT013"},{"cityName":"RAIPUR","code":"CT014"},{"cityName":"RAJNANDGAON","code":"CT015"},{"cityName":"SUKMA","code":"CT019"},{"cityName":"SURAJPUR","code":"CT026"},{"cityName":"SURGUJA","code":"CT016"}],"HP":[{"cityName":"BILASPUR","code":"HP001"},{"cityName":"CHAMBA","code":"HP002"},{"cityName":"HAMIRPUR","code":"HP003"},{"cityName":"KANGRA","code":"HP004"},{"cityName":"KINNAUR","code":"HP005"},{"cityName":"KULLU","code":"HP006"},{"cityName":"LAHUL & SPITI","code":"HP007"},{"cityName":"MANDI","code":"HP008"},{"cityName":"SHIMLA","code":"HP009"},{"cityName":"SIRMAUR","code":"HP010"},{"cityName":"SOLAN","code":"HP011"},{"cityName":"UNA","code":"HP012"}],"MN":[{"cityName":"BISHNUPUR","code":"MN001"},{"cityName":"CHANDEL","code":"MN002"},{"cityName":"CHURACHANDPUR","code":"MN003"},{"cityName":"EAST IMPHAL","code":"MN009"},{"cityName":"JIRIBAM","code":"MN016"},{"cityName":"KAKCHING","code":"MN014"},{"cityName":"KANGPOKPI","code":"MN012"},{"cityName":"NONEY","code":"MN013"},{"cityName":"PHERZAWL","code":"MN011"},{"cityName":"SENAPATI","code":"MN005"},{"cityName":"TAMENGLONG","code":"MN006"},{"cityName":"TENGNOUPAL","code":"MN010"},{"cityName":"THOUBAL","code":"MN007"},{"cityName":"UKHRUL","code":"MN008"},{"cityName":"WEST IMPHAL","code":"MN004"}],"JH":[{"cityName":"BOKARO","code":"JH001"},{"cityName":"CHATRA","code":"JH002"},{"cityName":"DEOGARH","code":"JH003"},{"cityName":"DHANBAD","code":"JH004"},{"cityName":"DUMKA","code":"JH005"},{"cityName":"EAST SINGHBHUM","code":"JH006"},{"cityName":"GARHWA","code":"JH007"},{"cityName":"GIRIDIH","code":"JH008"},{"cityName":"GODDA","code":"JH009"},{"cityName":"GUMLA","code":"JH010"},{"cityName":"HAZARIBAGH","code":"JH011"},{"cityName":"JAMTARA","code":"JH021"},{"cityName":"KHUNTI","code":"JH023"},{"cityName":"KODERMA","code":"JH012"},{"cityName":"LATEHAR","code":"JH019"},{"cityName":"LOHARDAGA","code":"JH013"},{"cityName":"PAKUR","code":"JH014"},{"cityName":"PALAMAU","code":"JH015"},{"cityName":"RAMGARH","code":"JH024"},{"cityName":"RANCHI","code":"JH016"},{"cityName":"SAHIBGANJ","code":"JH017"},{"cityName":"SARAIKELA KHARASAWAN","code":"JH022"},{"cityName":"SIMDEGA","code":"JH020"},{"cityName":"WEST SINGHBHUM","code":"JH018"}],"CH":[{"cityName":"CHANDIGARH","code":"CH001"}],"AR":[{"cityName":"CHANGLANG","code":"AR001"},{"cityName":"DIBANG VALLEY","code":"AR002"},{"cityName":"EAST KHAMENG","code":"AR003"},{"cityName":"EAST SIANG","code":"AR004"},{"cityName":"LOHIT","code":"AR005"},{"cityName":"LONGDING","code":"AR017"},{"cityName":"LOWER DIBANG VALLEY","code":"AR012"},{"cityName":"LOWER SUBANSIRI","code":"AR006"},{"cityName":"Namsai","code":"AR019"},{"cityName":"PAPUMPARE","code":"AR014"},{"cityName":"TAWANG","code":"AR007"},{"cityName":"TIRAP","code":"AR008"},{"cityName":"UPPER SIANG","code":"AR013"},{"cityName":"UPPER SIBANSIRI","code":"AR009"},{"cityName":"WEST KAMENG","code":"AR010"},{"cityName":"WEST SIANG","code":"AR011"}],"DL":[{"cityName":"Central Delhi","code":"DL005"},{"cityName":"Delhi Shahdara","code":"DL011"},{"cityName":"East Delhi","code":"DL004"},{"cityName":"New Delhi","code":"DL006"},{"cityName":"North Delhi","code":"DL002"},{"cityName":"North East Delhi","code":"DL003"},{"cityName":"North West Delhi","code":"DL001"},{"cityName":"South Delhi","code":"DL007"},{"cityName":"South East Delhi","code":"DL010"},{"cityName":"South West Delhi","code":"DL008"},{"cityName":"West Delhi","code":"DL009"}],"DD":[{"cityName":"DAMAN","code":"DD001"},{"cityName":"DIU","code":"DD002"}],"TR":[{"cityName":"DHALAI","code":"TR004"},{"cityName":"GOMATI","code":"TR007"},{"cityName":"KHOWAI","code":"TR005"},{"cityName":"NORTH TRIPURA","code":"TR001"},{"cityName":"SEPAHIJHALA","code":"TR006"},{"cityName":"SOUTH TRIPURA","code":"TR002"},{"cityName":"UNAKOTI","code":"TR008"},{"cityName":"WEST TRIPURA","code":"TR003"}],"NL":[{"cityName":"DIMAPUR","code":"NL008"},{"cityName":"KIPHERE","code":"NL010"},{"cityName":"KOHIMA","code":"NL001"},{"cityName":"LONGLENG","code":"NL009"},{"cityName":"MOKOKCHUNG","code":"NL002"},{"cityName":"MON","code":"NL003"},{"cityName":"PEREN","code":"NL011"},{"cityName":"PHEK","code":"NL004"},{"cityName":"TUENSANG","code":"NL005"},{"cityName":"WOKHA","code":"NL006"},{"cityName":"ZUNHEBOTO","code":"NL007"}],"SK":[{"cityName":"EAST DISTRICT","code":"SK001"},{"cityName":"NORTH DISTRICT","code":"SK002"},{"cityName":"SOUTH DISTRICT","code":"SK003"},{"cityName":"WEST DISTRICT","code":"SK004"}],"ML":[{"cityName":"EAST GARO HILLS","code":"ML001"},{"cityName":"EAST JAINTIA HILLS","code":"ML008"},{"cityName":"EAST KHASI HILLS","code":"ML002"},{"cityName":"NORTH GARO HILLS","code":"ML011"},{"cityName":"RI BHOI","code":"ML007"},{"cityName":"SOUTH GARO HILLS","code":"ML006"},{"cityName":"SOUTHWEST KHASI HILS","code":"ML010"},{"cityName":"WEST GARO HILLS","code":"ML004"},{"cityName":"WEST KHASI HILLS","code":"ML005"}],"PY":[{"cityName":"KARAIKAL","code":"PY003"},{"cityName":"MAHE","code":"PY001"},{"cityName":"PONDICHERRY","code":"PY002"},{"cityName":"YANAM","code":"PY004"}],"AN":[{"cityName":"NICOBAR","code":"AN003"},{"cityName":"NORTH&MIDDLE ANDAMAN","code":"AN002"},{"cityName":"SOUTH ANDAMAN","code":"AN001"}],"GO":[{"cityName":"NORTH GOA","code":"GO001"},{"cityName":"SOUTH GOA","code":"GO002"}],"DN":[{"cityName":"SILVASSA","code":"DN001"}]}';
    return $city;
}

function getStateforFuel(){
    $state = '{
        "AN": "Andaman and Nicobar",
        "AP": "Andhra Pradesh",
        "AR": "Arunachal Pradesh",
        "AS": "Assam",
        "BR": "Bihar",
        "CH": "Chandigarh",
        "CT": "Chhatisgarh",
        "DD": "Daman and Diu",
        "DL": "Delhi",
        "DN": "Dadra Nagarhaveli",
        "GJ": "Gujarat",
        "GO": "Goa",
        "HP": "Himachal Pradesh",
        "HR": "Haryana",
        "JH": "Jharkhand",
        "JK": "Jammu and Kashmir",
        "KA": "Karnataka",
        "KL": "Kerala",
        "MH": "Maharashtra",
        "ML": "Meghalaya",
        "MN": "Manipur",
        "MP": "Madhya Pradesh",
        "MZ": "Mizoram",
        "NL": "Nagaland",
        "OR": "Odisha",
        "PB": "Punjab",
        "PY": "Pondicherry",
        "RJ": "Rajasthan",
        "SK": "Sikkim",
        "TG": "Telangana",
        "TN": "Tamil Nadu",
        "TR": "Tripura",
        "UA": "Uttarakhand",
        "UP": "Uttar Pradesh",
        "WB": "West Bengal"
    }';
    return $state;
}

function getCityforHome(){
    $city = '{
        "GJ001": "AHMEDABAD",
        "GJ006": "BHAVNAGAR",
        "GJ008": "GANDHI NAGAR",
        "GJ021": "SURAT",
        "GJ024": "VADODARA",
        "MH003": "AMRAVATI",
        "MH004": "AURANGABAD",
        "MH012": "GREATER MUMBAI",
        "MH017": "NAGPUR",
        "MH020": "NASHIK",
        "MH022": "PUNE",
        "MH029": "THANE",
        "MP005": "BHOPAL",
        "MP014": "GUNA",
        "MP015": "GWALIOR",
        "MP018": "INDORE",
        "MP019": "JABALPUR",
        "MP042": "UJJAIN",
        "MZ007": "CHAMPHAI",
        "RJ001": "AJMER",
        "RJ008": "BIKANER",
        "RJ017": "JAIPUR",
        "RJ032": "UDAIPUR",
        "TG010": "HYDERABAD",
        "UP001": "AGRA",
        "UP002": "ALIGARH",
        "UP013": "BAREILLY",
        "UP017": "BULANDSHAHR",
        "UP028": "GHAZIABAD",
        "UP031": "GORAKHPUR",
        "UP041": "KANPUR URBAN",
        "UP043": "KUSHINAGAR",
        "UP046": "LUCKNOW",
        "UP050": "MATHURA",
        "UP052": "MEERUT",
        "UP070": "VARANASI"
    }';
    return $city;
}