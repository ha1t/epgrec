<?php
/**
 *
 *
 */

error_reporting( E_ALL | E_STRICT );

require_once dirname(dirname(__FILE__)) . '/config.php'; 

// DBにtable作る
$db = DB::conn('travis-ci');

$sql = file_get_contents(dirname(dirname(__FILE__)) . '/config/epgrec.sql'); 
$db->query($sql); 

$sql = file_get_contents(dirname(__FILE__) . '/testdata.sql'); 
$db->query($sql); 

/*
$sql = "SELECT * FROM Recorder_programTbl LIMIT 5";
$db = DB::conn();
$rows = $db->rows($sql); 
$insert_sql = '';
foreach ($rows as $row) {
    foreach ($row as $key => $value) {
        if (!is_numeric($value)) {
            $row[$key] = "'{$value}'";
        } 
    } 
    $values = implode(',', $row); 
    $insert_sql .= "INSERT INTO Recorder_programTbl VALUES ({$values});" . PHP_EOL;
} 

file_put_contents('testdata.sql', $insert_sql); 
 */
