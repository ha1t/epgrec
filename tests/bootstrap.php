<?php
/**
 *
 *
 */

error_reporting( E_ALL | E_STRICT );

if (file_exists(dirname(dirname(__FILE__)) . '/config.php')) {
    require_once dirname(dirname(__FILE__)) . '/config.php'; 
} else {
    require_once dirname(dirname(__FILE__)) . '/config_travisci.php'; 
} 

// DBにtable作る
function create_table() {
    $db = DB::conn('travis-ci');

    $sql = file_get_contents(dirname(dirname(__FILE__)) . '/config/epgrec.sql'); 
    $db->query($sql); 
} 

create_table(); 

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
