<?php
require_once 'NunaTest.php';

class dbTest extends NunaTest {
    public function testFirst() {
        $db = $this->import('db.master');
        
        $this->debug($db->query('show databases;')->result());
    }
}

$test = new dbTest();
$test->run();