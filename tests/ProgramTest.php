<?php
class ProgramTest extends PHPUnit_Framework_TestCase
{
    public function setUp() 
    {
        $db = DB::conn('travis-ci'); 
        $sql = file_get_contents(dirname(__FILE__) . '/testdata.sql'); 
        $db->query($sql); 
    }

    public function tearDown() 
    {
        $db = DB::conn('travis-ci'); 
        $sql = 'DELETE FROM Recorder_programTbl';
        $db->query($sql); 
    }

    public function testSearch() 
    {
        $rows = Program::search('', array()); 
        $this->assertTrue(is_array($rows)); 
    }

    public function testGetId() 
    {
        $rows = Program::search('', array()); 
        $row = current($rows);
        $this->assertEquals('program', strtolower(get_class(Program::get($row['program_disc'])))); 
        $this->assertFalse(Program::get('dummy')); 
    }
}
