<?php
class MyView {
    protected $template_dir = '../app/templates/';
    protected $layout = '../app/templates/layout/layout.phtml';
    public $vars = array();
    public $isAMP = false;
    public $request = array();
    public function __construct($template_dir = null, $layout = null) {
        if ($template_dir !== null) {
            // Check here whether this directory really exists
            $this->template_dir = $template_dir;
        }
        
        if ($layout !== null) {
            $this->layout = $layout;
        }
        $this->request = $_REQUEST;
    }
    public function render($template_file) {
        if (file_exists($this->template_dir.$template_file)) {
            include $this->layout;
        } else {
            throw new Exception('no template file ' . $template_file . ' present in directory ' . $this->template_dir);
        }
    }
    public function __set($name, $value) {
        $this->vars[$name] = $value;
    }
    public function __get($name) {
        return $this->vars[$name];
    }
}
?>