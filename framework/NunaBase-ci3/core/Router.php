<?php
/**
 * Description of MY_Router
 *
 * @author hoksi
 */

class CI_Router {
    public $class;
    public $method;
    protected $params;
    
    function __construct() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $params = explode('/', trim(str_replace($_SERVER['SCRIPT_NAME'], '', $path), '/'));
        $this->lass_method = array_shift($params);
        $this->method = $this->method ? $this->method : 'index';
        $this->params = $params;
    }

    function fetch_class() {
        return $this->class;
    }
    
    function fetch_method() {
        return $this->method;
    }
    
    function fetch_params() {
        return $this->params;
    }
    
    function set_class_name($class_name) {
        $this->class = $class_name;
        return $this;
    }
}