<?php
require_once('NunaTest.php');


class Test extends NunaTest {
    public function __construct() {
        parent::__construct();
    }
    
    public function test_first() {
        $this->_assert_true(false);
    }
    
    public function test_second() {
        $this->_assert_true(true);
    }
    
    public function testThird() {
        $this->assertTrueStrict(1);
    }
    
    public function testOr() {
        $this->assertTrue((PHP_SAPI === 'cli' OR defined('STDIN')));
    }
    
    public function testGetParams() {
        $this->assertTrue(!empty($this->router->fetch_params()));
    }
    
    public function testLoadDb() {
        $master = $this->import('db.master');
        $slave = $this->import('db.slave');
        
        $this->assertTrue(get_class($slave) == get_class($master));
    }
    
    public function testLoadCustomDb() {
        $db = $this->import('db', array(
        	'dsn'	=> '',
        	'hostname' => getenv('IP'),
        	'username' => getenv('C9_USER'),
        	'password' => '',
        	'database' => 'c9',
        	'dbdriver' => 'mysqli',
        	'dbprefix' => '',
        	'pconnect' => FALSE,
        	'db_debug' => (ENVIRONMENT !== 'production'),
        	'cache_on' => FALSE,
        	'cachedir' => '',
        	'char_set' => 'utf8',
        	'dbcollat' => 'utf8_general_ci',
        	'swap_pre' => '',
        	'encrypt' => FALSE,
        	'compress' => FALSE,
        	'stricton' => FALSE,
        	'failover' => array(),
        	'save_queries' => TRUE
        ));
        
        $this->assertTrue(get_class($db) == 'CI_DB_mysqli_driver');
    }
    
    public function testLoadDsn() {
        $dsn = 'mysqli://' . getenv('C9_USER') . ':@' . getenv('IP') . '/c9';
        
        $db = $this->import('db.' . $dsn);
        
        $this->assertTrue(get_class($db) == 'CI_DB_mysqli_driver');
    }
}

$test = new Test();
$test->run();