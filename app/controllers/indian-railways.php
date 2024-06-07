<?php

class Controller_indian_railways extends DB
{

    function __construct($__labels)
    {
        parent::__construct();

        $this->t = new MyView('../app/templates/rail/');
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

    public function pnr_status()
    {

        $this->t->pnr = isset($this->t->request['pnr']) ? $this->t->request['pnr'] : '';
        $this->t->pnrErr = '';
        $this->t->pnrStatus = '';
        $railModel = new Model_rail();

        $errMsg = array();
        if (isset($this->t->request['submit'])) {

            if ($this->t->pnr == '') {
                $this->t->pnrErr = 'Enter PNR no.';
                $errMsg[] = 'Enter PNR no.';
            } elseif (strlen($this->t->pnr) != 10) {
                $this->t->pnrErr = 'PNR Number should be 10 digit numeric number.';
                $errMsg[] = 'PNR Number should be 10 digit numeric number';
            }

            if (sizeof($errMsg) == 0) {
                $this->t->pnrStatus = $railModel->getPNRData(array('pnr' => $this->t->pnr));               
            }
        }
        $this->t->meta_tags = array(
            'title' => "PNR Status Indian Railways: " . PROJECT_NAME,
            'description' => "PNR Status Indian Railways : " . PROJECT_NAME,
            'keywords' => PROJECT_NAME,
        );

        $this->t->render('pnr_status_detail.phtml');
    }

    public function train_schedule()
    { 
        $railModel = new Model_rail();
        $this->t->schedule = $railModel->getTrainScheduleData(array('train_no' => $this->t->request["train_no"]));
        $this->t->meta_tags = array(
            'title' => "Train Schedule Indian Railways: " . PROJECT_NAME,
            'description' => "Train Schedule Indian Railways : " . PROJECT_NAME,
            'keywords' => PROJECT_NAME,
        );
       
        $this->t->render('train_schedule.phtml');
    }

    public function live_train_status(){
        //echo "<div style='color:red;font-size:200px'>Error</div>";
        //p($this->t->request); exit;
        $this->t->meta_tags = array(
            'title' => "Train Live Status Indian Railways: " . PROJECT_NAME,
            'description' => "Train Live Status Indian Railways : " . PROJECT_NAME,
            'keywords' => PROJECT_NAME,
        );
        $this->t->trainNoErr="";
        $this->t->livestatus="";
        $this->t->schedule="";
        $this->t->trainno = isset($this->t->request['trainno']) ? $this->t->request['trainno'] : '12559';
        $this->t->date = isset($this->t->request['date']) ? $this->t->request['date'] : date('Y-m-d');
        $params = array('trainno' => $this->t->trainno, 'date' => $this->t->date);
        
        $errMsg = array();
        if (isset($this->t->request['submit'])) {

            if ($this->t->trainno == '') {
                $this->t->trainNoErr = 'Enter Train no.';
                $errMsg[] = 'Enter Train no.';
            } 

            if (sizeof($errMsg) == 0) {
                $railModel = new Model_rail();
                $this->t->livestatus = $railModel->RYliveStatus($params);
                if($this->t->livestatus['error']==1){
                    $this->t->livestatus = $railModel->RYliveStatus($params);
                }
                $this->t->livestatus = $this->t->livestatus['data'];               
                if(count($this->t->livestatus['runnStations'])==0){  
                    $this->t->schedule = $railModel->getTrainScheduleData(array('train_no' => $this->t->trainno));
                }
            }

        }
        
        
        $this->t->render('live_status.phtml');
    }
}