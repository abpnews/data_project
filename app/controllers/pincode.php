<?php

class Controller_pincode extends DB
{

    function __construct($__labels)
    {
        parent::__construct();

        $this->t = new MyView('../app/templates/pincode/');
        $this->t->page = 'home';
        $this->t->meta_tags = array(
            'title' => "Welcome to " . PROJECT_NAME,
            'description' => "welcome to " . PROJECT_NAME,
            'keywords' => PROJECT_NAME,
            'shareURL' => ''
        );
        $this->t->breadcrumbs = array();
        header("X-Frame-Options: SAMEORIGIN");
        set_page_cache_headers(5);
    }

    function __destruct()
    {
        parent::__destruct();
    }

    
    /*** Pin code home page ***/
    function index() {
       
        $this->t->page = 'pin-code-home';
               
        $pin_code_obj = new Model_pincode();
        $this->t->states= $pin_code_obj->getAllState();
              
        $this->t->render('home.phtml');
    }
    
    function pincodesearch() {
        $pincode = $this->t->request['pincode'];
        $this->t->page = 'pin-code-home';
               
        $pin_code_obj = new Model_pincode();
        $params = array('pin_code'=>$pincode);    
        $officeLists= $pin_code_obj->postOfficeListByPincode($params);
        $this->t->pincode = $pincode;
        $this->t->officeLists = $officeLists;
               
        $this->t->render('pincodesearch.phtml');
    }
    
        
}