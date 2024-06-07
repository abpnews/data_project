<?php
//print_r($_REQUEST);
//print_r($_SERVER);exit();
error_reporting(E_ERROR);

set_time_limit(0);
require_once ('../app/includes/config.php');
require_once ('../app/includes/constants.php');
require_once ('../app/includes/functions.php');
require_once ('../app/includes/MyView.php');
require_once ('../app/includes/routes.php');
require_once ('../app/includes/database.php');

date_default_timezone_set(TIMEZONE);

defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

$error = array();

foreach ($CONFIG['controllers'] as $c) {
    require_once ("../app/controllers/$c.php");
}

foreach ($CONFIG['models'] as $m) {
    require_once ("../app/models/$m.php");
}


if (!isset($_SESSION['isLogin'])) {
    $_SESSION['isLogin'] = '';
}

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    $_SERVER['PHP_AUTH_USER'] = '';
}

if (!isset($_SERVER['PHP_AUTH_PW'])) {
    $_SERVER['PHP_AUTH_PW'] = '';
}


$_REQUEST = array_merge($_GET, $_POST, $_REQUEST);
if (count($_REQUEST) > 0) {
    foreach ($_REQUEST as $k => $r) {
        if (is_array($r)) {
            array_walk($r, function(&$value, $key, $joinUsing) {
                $value = filter_var($value, FILTER_UNSAFE_RAW);
            }, '');
            $_REQUEST[$k] = $r;
        } else {
            $_REQUEST[$k] = filter_var($_REQUEST[$k], FILTER_UNSAFE_RAW);
        }
    }
}

if (isset($_SERVER['argv'][3])) {
    $arr = array();
    foreach ($_SERVER['argv'] as $k => $v) {
        if ($k > 2) {
            $arr["__argv" . ($k - 2)] = $v;
        }
    }
    $_REQUEST = array_merge($_REQUEST, $arr);
}

//p($GLOBALS);

$q = isset($_REQUEST['_q']) ? ltrim($_REQUEST['_q'], '/') : ((isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '') . (isset($_SERVER['argv'][2]) ? '/' . $_SERVER['argv'][2] : ''));
$q = @array_shift(explode('?', $q));



define('BASE_URL', BASE_URL_BASE);

/*
$tmp = explode('/', $q);
array_shift($tmp);
$q = implode('/',$tmp);
*/
$URL_EXTRA = '';

//require_once ('../app/includes/lang_' . LANG . '.php');
$__labels = array();

//ob_start("sanitize_output");
route($q, $__labels);
//ob_end_flush();
?>