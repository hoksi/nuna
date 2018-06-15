<?php
/**
 * Description of MY_Router
 *
 * @author hoksi
 */

class CI_Router {
    protected $class_name;
    protected $class_method;
    protected $params;
    
    function __construct() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basename = basename($_SERVER['SCRIPT_NAME']);

        if(strpos($path, $basename) === false) {
            $path = (rtrim($path, '/')) . '/' . $basename;
        }

        $params = explode('/', trim(str_replace($_SERVER['SCRIPT_NAME'], '', $path), '/'));
        $this->class_method = array_shift($params);
        $this->class_method = $this->class_method ? $this->class_method : 'index';
        $this->params = $params;
    }

    function fetch_class() {
        return $this->class_name;
    }
    
    function fetch_method() {
        return $this->class_method;
    }
    
    function fetch_params() {
        return $this->params;
    }
    
    function set_class_name($class_name) {
        $this->class_name = $class_name;
        return $this;
    }
}