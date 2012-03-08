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

    public function testGet()
    {
        $this->assertFalse(Category::get(''));

        $result = Category::getAll(); 
        if (count($result) > 0) {
            $row = current($result); 
            $category = Category::get($row->category_disc);
            $this->assertEquals('Category', get_class($category)); 
        } 
    } 
}
