<?php

class Controller_fuel_price extends DB
{

    function __construct($__labels)
    {
        parent::__construct();

        $this->t = new MyView('../app/templates/fuel/');
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

    public function index()
    {
        $today = date('H') >= 6 ? date('Y-m-d') : date('Y-m-d', strtotime('-1 day'));
        $key = "fuel/daily/{$today}/fuel.json";
        $prms = array('bucket' => "devsquad", 'key' => $key);
        $this->t->fuel = pull2s3($prms);        
        $this->t->state = json_decode(getStateforFuel(),true);
        $this->t->city = json_decode(getCityforHome(),true);
       
        $this->t->render('home.phtml');
        
    }

    public function statewise(){
        $today = date('H') >= 6 ? date('Y-m-d') : date('Y-m-d', strtotime('-1 day'));
        $key = "fuel/daily/{$today}/fuel.json";
        $prms = array('bucket' => "devsquad", 'key' => $key);
        $this->t->fuel = pull2s3($prms); 

        $stateUrl = str_replace("/fuel/","",$this->t->request['_q']);
        $stateUrl = str_replace("-statewise","",$stateUrl);
        $stateCode = explode("-",$stateUrl);
        $state = json_decode(getStateforFuel(),true);        
        $this->t->state = $state[$stateCode[0]];   
        $city = json_decode(getCityforFuel(),true);        
        $this->t->city = $city[$stateCode[0]];
        $this->t->render('statewise.phtml');
    }

    public function citywise(){
        $today = date('H') >= 6 ? date('Y-m-d') : date('Y-m-d', strtotime('-1 day'));
        $key = "fuel/daily/{$today}/fuel.json";
        $prms = array('bucket' => "devsquad", 'key' => $key);
        $this->t->fuel = pull2s3($prms);

        $cityUrl = str_replace("/fuel/","",$this->t->request['_q']);
        $cityUrl = str_replace("-cityewise","",$cityUrl);
        $cityCode = explode("-",$cityUrl);

        $fuel = json_decode($this->t->fuel, true);
        $this->t->cityFuel = $fuel[$cityCode[0]];
        $this->t->render('citywise.phtml');
    }

   
}