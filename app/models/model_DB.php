<?php

class Model_model_DB extends DB{

    protected $retryTimes = 1;
    protected $debug = 1;

    function __construct() {
        parent::__construct();
    }

    public function setValue($params = array()){
        $ret = $this->get_scalar("SELECT {$params['column']} FROM {$params['table']} WHERE ". implode(" =? AND ",$params['where_where']).'=?',$params['where_set_arr']);
        
        return $ret;
    }
    

}

?>