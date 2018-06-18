<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Nuna extends CI_Controller {
    public $router;
    public $class_name;
    
    protected $runMethod;
    protected $runClass;
    protected $runMode;
    protected $profilerMode;

    public function __construct($runMode = false, $profilerMode = false) {
        parent::__construct();
        
        $this->runMode = $runMode;
        $this->profilerMode = $profilerMode;
        $this->class_name = get_class($this);
        
        $this->router->set_class_name($this->class_name);
        
        $this->runClass = $this->router->fetch_class();
        $this->runMethod = $this->router->fetch_method();
    }
    
    public function import($resource, $params = null, $return = false) {
    	return $this->load->import($resource, $params, $return);
    }

    public function run($profiler = false) {
        if(method_exists($this, '_remap')) {
            $this->_remap($this->runMethod, $this->router->fetch_params());
        } elseif(method_exists($this, $this->runMethod)) {
            call_user_func_array(array($this, $this->runMethod), $this->router->fetch_params());
        } elseif($this->class_name != 'Nuna') {
        	show_error("The page you requested was not found. ({$this->runClass}/{$this->runMethod})");
        }

        if($profiler) {
            $this->output->enable_profiler();
        }
        
        $this->benchmark->mark('total_execution_time_end');
        $this->output->_display();
    }
    
    public function __destruct() {
        if (class_exists('CI_DB') AND isset($this->db)) {
            $this->db->close();
        }

        if($this->runMode) {
            $this->run($this->profilerMode);
        }
    }
}