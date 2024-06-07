<?php

/*
 * This class is basically for all admin opertaions
 * 
 */

class Controller_Admin extends DB {

    function __construct($__labels) {
        parent::__construct();
        //p('kkss');
        $this->t = new MyView('../app/templates/admin/','../app/templates/layout/admin.phtml');
        $this->t->page = 'admin';
        $this->t->__labels = $__labels;
        //p($this->t->news);
        $this->t->meta_tags = array(
            'title' => "Welcome to " . PROJECT_NAME,
            'description' => "welcome to " . PROJECT_NAME,
            'keywords' => PROJECT_NAME
        );
        $this->t->breadcrumbs = array();
        header("X-Frame-Options: SAMEORIGIN");
        set_page_cache_headers(0);
    }

    function __destruct() {
        parent::__destruct();
    }

    public function index()
    {
        $source = isset($this->t->request['source'])?$this->t->request['source']:0;
        $parse_others = isset($this->t->request['parse_others'])?$this->t->request['parse_others']:1;
        $limit = isset($this->t->request['limit'])?$this->t->request['limit']:3000;

        $source_where = $source?" AND s.id = $source ":"";

        
    }

}
