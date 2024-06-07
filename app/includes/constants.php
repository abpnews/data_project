<?php

if (APPLICATION_ENV == 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    define('SERVER_PREFIX', '');
    define('S3_PREFIX', 'dev/');
    
} elseif (APPLICATION_ENV == 'staging') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    define('SERVER_PREFIX', '');
    define('S3_PREFIX', 'stage/');

} elseif (APPLICATION_ENV == 'production') {

    define('SERVER_PREFIX', '');
    define('S3_PREFIX', '');

}

define('REGION','ap-south-1');

define('VERSION', '20210606-03');
define('JS_VERSION', '20210725-11');
define('CSS_VERSION', '20210606-09');
define('IMG_VERSION', '20210606-09');

define('PROJECT_NAME', 'free-best-tools.com');
define('PROJECT_TITLE', 'free-best-tools.com');
define('TIMEZONE', 'Asia/Kolkata');
define('TIMEZONE_SQL', '+05:30');

define('CACHE_PATH', CACHE_ROOT . '/cache');
define('LOG_PATH', CACHE_ROOT . '/logs');

$CONFIG['controllers'] = array("home", "api", "admin","cron","indian-railways","fuel-price","pincode","calculator");
$CONFIG['models'] = array("model_rail","model_DB","railCaptcha","model_fuel","model_pollution","model_pincode");
?>