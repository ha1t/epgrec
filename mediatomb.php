#!/usr/bin/php
<?php

include_once('config.php');
include_once(INSTALL_PATH.'/DBRecord.class.php');
include_once(INSTALL_PATH.'/reclib.php');

try {

  $recs = DBRecord::createRecords(TBL_PREFIX.RESERVE_TBL );

// DB接続
  $dbh = mysql_connect( DB_HOST, DB_USER, DB_PASS );
  if( $dbh === false ) exit( "mysql connection fail" );
  $sqlstr = "use ".DB_NAME;
  mysql_query( $sqlstr );
  $sqlstr = "set NAME utf8";
  mysql_query( $sqlstr );

  foreach( $recs as $rec ) {
	  $title = mysql_real_escape_string($rec->title)."(".date("Y/m/d", toTimestamp($rec->starttime)).")";
      $sqlstr = "update mt_cds_object set metadata='dc:description=".mysql_real_escape_string($rec->description)."' where dc_title='".$rec->path."'";
      mysql_query( $sqlstr );
      $sqlstr = "update mt_cds_object set dc_title='".$title."' where dc_title='".$rec->path."'";
      mysql_query( $sqlstr );
  }
}
catch( Exception $e ) {
    exit( $e->getMessage() );
}
?>