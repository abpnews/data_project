<?php

function route($url, $__labels) {
    //p('code: '.$url);
    
    $url = rtrim($url, '/');
    $url_array = array();
    $url_array = explode("/", $url);
    $default_controller = 'home';
    $default_action = 'index';
    $controller = $default_controller;
    $action = $default_action;
    $_params = array();
    $action_params = array();

    
    //p($url);
    if((preg_match('/^category\/(.*)\-(\d+)/', $url, $matches)) ? true : false){
        $action_params['title'] = $matches[1];
        $action_params['id'] = $matches[2];
        $controller = 'home';
        $action = 'category';
    } elseif((preg_match('/^template\/(.*)\-(\d+)/', $url, $matches)) ? true : false){
        $action_params['title'] = $matches[1];
        $action_params['id'] = $matches[2];
        $controller = 'home';
        $action = 'index';
    }elseif (preg_match("/-statewise$/", $url)) {
        $controller = 'fuel_price';
        $action = 'statewise';
    }elseif (preg_match("/-citywise$/", $url)) {
        $controller = 'fuel_price';
        $action = 'citywise';
    }elseif (preg_match("/pincodesearch$/", $url)) {
        $controller = 'pincode';
        $action = 'pincodesearch';  
    } elseif (count($url_array) == 1) {
        $action = !empty($url_array[0]) ? $url_array[0] : $default_action;
        $action = str_replace('-', '_', $action);
        if (!method_exists("Controller_{$controller}", str_replace('-', '_', $action))) {            
            if (method_exists("Controller_{$action}", $default_action)) {
                $controller = $action;
                $action = $default_action;
            }else{
                $action = 'error404';
            }
        }
    } elseif (count($url_array) == 2) {
        $controller = $url_array[0];
        $controller = str_replace('-', '_', $controller);
        $action = $url_array[1];        
        if (!method_exists("Controller_{$controller}", str_replace('-', '_', $action))) {
            $controller = 'home';
            $action = $url_array[0];
            $_params[$action] = '';
        }
    } elseif (count($url_array) > 2) {
        $controller = $url_array[0];
        $controller = str_replace('-', '_', $controller);        
        $action = $url_array[1];
        if (!method_exists("Controller_{$controller}", str_replace('-', '_', $action))) {
            $controller = 'home';
            $action = $url_array[0];
            array_shift($url_array);
        } else {
            array_shift($url_array);
            array_shift($url_array);
        }
        for ($i = 0; $i < count($url_array); $i += 2) {
            $_params[$url_array[$i]] = filter_var($url_array[($i + 1)], FILTER_SANITIZE_STRING);
        }
    }
    //  echo count($url_array); 
    $_REQUEST = array_merge($_REQUEST, $_params);

    if (method_exists("Controller_{$controller}", str_replace('-', '_', $action))) {
        $action = str_replace('-', '_', $action);
        $controller = "Controller_{$controller}";
        $class_name = new $controller($__labels);
        $class_name->$action($action_params);
    }else {
        $class_name = new Controller_Home($__labels);
        $class_name->error404();
    }
}
