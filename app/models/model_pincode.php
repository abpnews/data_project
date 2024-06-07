<?php

class Model_pincode extends DB {

    public function __construct($link = 0) {
        parent::__construct($link);
    }

    public function __destruct() {
        parent::__destruct();
    }

    public function getAllState() {
        $all_state = $this->query_to_array("SELECT distinct(state_slug),StateName as state_name from pincode_details ORDER BY StateName ASC");
        return $all_state;
    }
    
    public function isStateExist($params=array()) {
        $state = $this->get_row("SELECT id,StateName as state_name, state_slug from pincode_details where state_slug = ? LIMIT 1", array('s',$params['slug_state']));
        return $state;
    }

    public function isDistrictExist($params=array()) {
        $district = $this->get_row("SELECT id,District as district_name, district_slug from pincode_details where district_slug = ? LIMIT 1", array('s',$params['slug_district']));
        return $district;
    }

    public function isOfficeExist($params=array()) {
        $office = $this->get_row("SELECT id,OfficeName as office_name, office_slug from pincode_details where office_slug = ? LIMIT 1", array('s',$params['slug_office']));
        return $office;
    }
    
    public function getOfficeDetails($whereCond,$params) {
        if(count($params)==1){
            $offices = $this->query_to_array("SELECT *,CONCAT(state_slug,'/',district_slug,'/',office_slug) as office_detail_url from pincode_details
                    where $whereCond",array('s',$params[0]));
        }elseif(count($params)==2){
            $offices = $this->query_to_array("SELECT *,CONCAT(state_slug,'/',district_slug,'/',office_slug) as office_detail_url from pincode_details
                    where $whereCond",array('ss',$params[0],$params[1]));
        }elseif(count($params)==3){
           $offices = $this->get_row("SELECT * from pincode_details where $whereCond", array('sss', $params[0], $params[1], $params[2]));
        }
        return $offices;
    }

    public function getDistricts($params=array()) {
        $districts = $this->query_to_array("SELECT DISTINCT(District),CONCAT(state_slug,'/',district_slug) as url,state_slug,district_slug, District as district_name from pincode_details where state_slug =? ORDER BY District ASC", array('s',$params['slug_state']));
        return $districts;
    }

    public function getPostOffices($params=array()) {
        $offices = $this->query_to_array("SELECT DISTINCT(OfficeName),CONCAT(state_slug,'/',district_slug,'/',office_slug) as url,state_slug,district_slug, office_slug,OfficeName as office_name from pincode_details where state_slug = ? AND district_slug =? ORDER BY OfficeName ASC", array('ss',$params['slug_state'],$params['slug_district']));
        return $offices;
    }

    public function postOfficeListByPincode($params=array()) {
        $postOfficeList = $this->query_to_array("SELECT CONCAT(state_slug,'/',district_slug,'/',office_slug) as url , OfficeName as office_name,CircleName as circle_name,District as district from pincode_details where Pincode = ?",array('s',$params['pin_code']));
        return $postOfficeList;
    }

}
?>