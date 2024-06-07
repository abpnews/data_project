<?php

/*
 * This is an API endpoint for all data points
 */

class Controller_Api extends DB
{

    function __construct()
    {
        
        set_page_cache_headers(0);
        parent::__construct();
        $this->t = new MyView('../app/templates/home/');
        header('Content-type: application/json');
        
        $this->key = isset($this->t->request['api-key']) ? $this->t->request['api-key'] : '';
        $this->client = apiKeyValidator($this->key);
        
        if ($this->client == '') {
            exit('{"error":"401","errorMsg":"Unauthorized access"}');
        }
    }

    function __destruct()
    {
        parent::__destruct();
    }

    
    function index()
    {
        //p($this->t->request);
        
        $return = array('error' => $ret['error'], 'errorcode' => $ret['errorcode'], 'data' => json_decode($ret['data']), 'params' => array());
        $this->__op($return);
    }

    private function __op($return = array()){
        //TO-DO maintain the log
        exit(json_encode($return));
    }
}
