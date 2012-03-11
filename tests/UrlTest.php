<?php
/**
 *
 *
 */
class UrlTest extends PHPUnit_Framework_TestCase
{
    public function testRoot() 
    {
        $this->assertEquals(APP_URL, url('/')); 
    }

    public function testIndex() 
    {
        $this->assertEquals(APP_URL . 'top/index', url('top/index')); 
    }
}
