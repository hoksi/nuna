<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once THIRDPARTYPATH . 'Template_.2.2.8/Template_.class.php';

class CI_Tpl extends Template_ {
    protected $orgTemplateDir = false;

    public function __construct($config = array()) {
        if(!empty($config)) {
            foreach($config as $key => $val) {
                if(isset($this->{$key})) {
                    $this->{$key} = $val;
                }
            }
        }
    }
    
    public function fatch($fid) {
        $fatchData = parant::fatch($fid);
        
        $this->unsetCustomTemplateDir();
        
        return $fatchData;
    }
    
    public function print_($fid, $scope = '', $sub = false) {
        parent::print_($fid, $scope, $sub);
        
        $this->unsetCustomTemplateDir();
    }
    
    public function setCustomTemplateDir($customTemplateDir)
    {
        $this->orgTemplateDir = $this->template_dir;
        $this->template_dir = $customTemplateDir;
        
        return $this;
    }
    
    protected function unsetCustomTemplateDir()
    {
        if($this->orgTemplateDir !== false) {
            $this->template_dir = $this->orgTemplateDir;
            $this->customTemplateDir = false;
        }
        
        return $this;
    }
}