<?php

class Controller_Home extends DB
{

    function __construct($__labels)
    {
        parent::__construct();
        $this->t = new MyView('../app/templates/home/');
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

        $this->r = new MyView('../app/templates/rail/');
        $this->r->page = 'home';
        $this->r->meta_tags = array(
            'title' => "Welcome to " . PROJECT_NAME,
            'description' => "welcome to " . PROJECT_NAME,
            'keywords' => PROJECT_NAME,
            'shareURL' => ''
        );
    }

    function __destruct()
    {
        parent::__destruct();
    }

    public function index()
    {
        $this->salary_hike_percentage();
    }
    function salary_hike_percentage()
    {
        //p($this->t->request);
        $this->t->osal = isset($this->t->request['osal']) ? $this->t->request['osal'] : '';
        $this->t->nsal = isset($this->t->request['nsal']) ? $this->t->request['nsal'] : '';
        $this->t->finalPercentage = 0;
        $this->t->osalErr = '';
        $this->t->nsalErr = '';
        $errMsg = array();
        if (isset($this->t->request['submit'])) {
            if ($this->t->osal == '') {
                $this->t->osalErr = 'Please enter Old salary.';
                $errMsg[] = 'Please enter Old salary.';
            }
            if ($this->t->nsal == '') {
                $this->t->nsalErr = 'Please enter New salary.';
                $errMsg[] = 'Please enter New salary.';
            }

            if (sizeof($errMsg) == 0) {
                $calculation = (($this->t->nsal - $this->t->osal) / $this->t->osal * 100);
                $this->t->finalPercentage = number_format((float) $calculation, 2, '.', '');
            }
        }

        $this->t->meta_tags = array(
            'title' => "Salary Hike Percentage Calculator : " . PROJECT_NAME,
            'description' => "Salary Hike Percentage Calculator : " . PROJECT_NAME,
            'keywords' => PROJECT_NAME,
        );
        

        $this->t->render('salary_hike_percentage.phtml');
    }

    public function tesst()
    {
        $today = date('H') >= 6 ? date('Y-m-d') : date('Y-m-d', strtotime('-1 day'));
        $key = "fuel/daily/{$today}/fuel.json";
        $prms = array('bucket' => "devsquad", 'key' => $key);
        $data = pull2s3($prms);
        p($data);
    }

    public function pollution(){
        
        $today = date('Y-m-d');
        $key = "aqi/daily/{$today}/aqi.json";
        $prms = array('bucket' => "devsquad", 'key' => $key);
        $data = pull2s3($prms);
        p($data);
    }

    public function about_us(){
        $this->t->render('about_us.phtml');
    }

    public function privacy_policy(){
        $this->t->render('privacy_policy.phtml');
    }


    public function error404(){
        $this->t->meta_tags = array(
            'title' => "404 Page not found : " . PROJECT_NAME,
            'description' => "404 Page not found : " . PROJECT_NAME,
            'keywords' => PROJECT_NAME,
        );
        http_response_code(404);
        $this->t->render('404.phtml');
    }
}
