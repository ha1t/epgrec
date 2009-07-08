<?php
include_once('config.php');
include_once( INSTALL_PATH . '/DBRecord.class.php' );
include_once( INSTALL_PATH . '/Reservation.class.php' );
include_once( INSTALL_PATH . '/reclib.php' );

$program_id = 0;
$reserve_id = 0;

if( isset($_GET['program_id'])) {
	$program_id = $_GET['program_id'];
}
else if(isset($_GET['reserve_id'])) {
	$reserve_id = $_GET['reserve_id'];
	try {
		$rec = new DBRecord( TBL_PREFIX.RESERVE_TBL, "id" , $reserve_id );
		$program_id = $rec->program_id;
	}
	catch( Exception $e ) {
		// 無視
	}
}

// 手動取り消しのときには、その番組を自動録画対象から外す
if( $program_id ) {
	try {
		$rec = new DBRecord(TBL_PREFIX.PROGRAM_TBL, "id", $program_id );
		$rec->autorec = 0;
	}
	catch( Exception $e ) {
		// 無視
	}
}

// 予約取り消し実行
try {
	Reservation::cancel( $reserve_id, $program_id );
}
catch( Exception $e ) {
	exit( "Error" . $e->getMessage() );
}
exit();
?>