<?php
/**
 *
 *
 */

class CategoryTest extends PHPUnit_Framework_TestCase
{
    public function testGetAll()
    {
        $result = Category::getAll(); 
        $this->assertTrue(is_array($result)); 
    } 
}
