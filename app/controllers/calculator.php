<?php

class Controller_Calculator extends DB
{

    function __construct($__labels)
    {
        parent::__construct();
        $this->t = new MyView('../app/templates/calculator/');
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
    function age_calculator()
    {
        //p($this->t->request);
        $this->t->dob = isset($this->t->request['dob']) ? $this->t->request['dob'] : '';
        $this->t->eod = isset($this->t->request['eod']) ? $this->t->request['eod'] : date('Y-m-d');
        $this->t->age = '';
        $this->t->dobErr = '';
        $this->t->eodErr = '';
        $errMsg = array();
        if (isset($this->t->request['submit'])) {
             if ($this->t->dob == '') {
                $this->t->dobErr = 'Please enter dob.';
                $errMsg[] = 'Please enter your dob.';
            }
            if ($this->t->eod == '') {
                $this->t->eodErr = 'Please enter the end date.';
                $errMsg[] = 'Please enter the end date.';
            } 

            if (sizeof($errMsg) == 0) {
                $from = new DateTime($this->t->dob);
                $to   = new DateTime($this->t->eod);
                $age = $from->diff($to);
               // $calculation = (($this->t->nsal - $this->t->osal) / $this->t->osal * 100);
                $this->t->age= "Your current age is ".$age->y." years ".$age->m." months ".$age->d." days.";
            }
        }

        $this->t->meta_tags = array(
            'title' => "Age Calculator : " . PROJECT_NAME,
            'description' => "Age Calculator : " . PROJECT_NAME,
            'keywords' => PROJECT_NAME,
        );
        

        $this->t->render('age_calculator.phtml');
    }
    function pf_calculator()
    {
        //p($this->t->request);
        $this->t->basic = isset($this->t->request['basic']) ? $this->t->request['basic'] : '';
        $this->t->da = isset($this->t->request['da']) ?  $this->t->request['da'] : '';
        $this->t->employeeContribution = '';
        $this->t->employerContribution = '';
        $this->t->totalContribution = '';
        $this->t->basicErr = '';
        $this->t->daErr = '';
        $errMsg = array();
        if (isset($this->t->request['submit'])) {
             if ($this->t->basic == '') {
                $this->t->basicErr = 'Please enter your basic salary.';
                $errMsg[] = 'Please enter your basic salary';
            }
            if ($this->t->da == '') {
                $this->t->daErr = 'Please enter your DA.';
                $errMsg[] = 'Please enter your DA.';
            } 

            if (sizeof($errMsg) == 0) {
                $employeeContribution=(0.12 * ($this->t->basic + $this->t->da )) ; 
                $employerContribution=((3.67/100)*($this->t->basic + $this->t->da) );
                $totalContribution=$employeeContribution+$employerContribution;

                $this->t->employeeContribution= "Employee Contribution towards EPF: ₹ ".$employeeContribution;
                $this->t->employerContribution= "Employer Contribution towards EPF: ₹ ".$employerContribution;
                $this->t->totalContribution="Total Contribution towards EPF: ₹ ".$totalContribution;
            }
        }

        $this->t->meta_tags = array(
            'title' => "PF Calculator : " . PROJECT_NAME,
            'description' => "PF Calculator : " . PROJECT_NAME,
            'keywords' => PROJECT_NAME,
        );
        

        $this->t->render('pf_calculator.phtml');
    }

    function gratuity_calculator()
    {
        //p($this->t->request);
        $this->t->basic = isset($this->t->request['basic']) ? $this->t->request['basic'] : '';
        $this->t->tenure = isset($this->t->request['tenure']) ?  $this->t->request['tenure'] : '';
        $this->t->gratuity = '';
     
        $this->t->basicErr = '';
        $this->t->tenureErr = '';
        $errMsg = array();
        if (isset($this->t->request['submit'])) {
             if ($this->t->basic == '') {
                $this->t->basicErr = 'Please enter your basic salary.';
                $errMsg[] = 'Please enter your basic salary';
            }
            if ($this->t->tenure == '') {
                $this->t->tenureErr = 'Please enter your tenure.';
                $errMsg[] = 'Please enter your tenure.';
            } 

            if (sizeof($errMsg) == 0) {
            
                 $gratuity=((15 * $this->t->basic * $this->t->tenure) / 30);
                 $this->t->gratuity= "Employee Gratuity : ₹ ".$gratuity;
            
            }
        }

        $this->t->meta_tags = array(
            'title' => "Gratuity Calculator : " . PROJECT_NAME,
            'description' => "Gratuity Calculator : " . PROJECT_NAME,
            'keywords' => PROJECT_NAME,
        );
        

        $this->t->render('gratuity_calculator.phtml');
    }
    function sip_calculator()
    {
        //p($this->t->request);
        $this->t->amount = isset($this->t->request['amount']) ? $this->t->request['amount'] : '';
        $this->t->tenure = isset($this->t->request['tenure']) ?  $this->t->request['tenure'] : '';
        $this->t->rate = isset($this->t->request['rate']) ?  $this->t->request['rate'] : '';
        $this->t->sip = '';
     
        $this->t->amountErr = '';
        $this->t->tenureErr = '';
        $this->t->rateErr = '';
        $errMsg = array();
        if (isset($this->t->request['submit'])) {
             if ($this->t->amount == '') {
                $this->t->amountErr = 'Please enter invested amount.';
                $errMsg[] = 'Please enter invested amount';
            }
            if ($this->t->rate == '') {
                $this->t->rateErr = 'Please enter expected return rate (p.a).';
                $errMsg[] = 'Please enter expected return rate (p.a).';
            } 

            if ($this->t->tenure == '') {
                $this->t->tenureErr = 'Please enter your tenure in number of years.';
                $errMsg[] = 'Please enter your tenure in number of years.';
            } 

            if (sizeof($errMsg) == 0) {
             //  $this->t->amount * ({[1 +  $this->t->rate] *  $this->t->tenure – 1} /  $this->t->rate) × (1 +  $this->t->rate)
            //$rate= ($this->t->rate/100)/($this->t->tenure*12);
                 //$sip=$this->t->amount * (((1 + $rate) *  ($this->t->tenure*12) - 1) /  $rate) * (1 +  $rate);
                 $sip=round((pow((1 + (pow((1 + $this->t->rate / 100), (1 / 12)) - 1)), ($this->t->tenure * 12)) - 1) / (pow((1+ $this->t->rate / 100), (1 / 12)) - 1) * $this->t->amount);
                 $mvalue= $sip-($this->t->amount * ($this->t->tenure*12));
                 $investedAmt=($this->t->amount * ($this->t->tenure*12));
                 $this->t->investedAmt= "Total invested amount : ₹ ".$investedAmt;
                 $this->t->sip= "Est. Return : ₹ ".$sip;
                 $this->t->mvalue= "Maturity Value : ₹ ".$mvalue;
                 
            }
        }

        $this->t->meta_tags = array(
            'title' => "Gratuity Calculator : " . PROJECT_NAME,
            'description' => "Gratuity Calculator : " . PROJECT_NAME,
            'keywords' => PROJECT_NAME,
        );
        

        $this->t->render('sip_calculator.phtml');
    }
}
