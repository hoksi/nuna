<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_member extends Nuna_Model {
    public function __construct() {
        parent::__construct();
    }
    
    public function getMember() {
        return array('Custom member');
    }
}