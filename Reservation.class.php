<?php
include_once('config.php');
include_once( INSTALL_PATH . "/DBRecord.class.php" );
include_once( INSTALL_PATH . "/reclib.php" );

// 予約クラス

class Reservation {
	
	public static function simple( $program_id , $autorec = 0, $mode = 0) {
		$rval = 0;
		try {
			$prec = new DBRecord( TBL_PREFIX.PROGRAM_TBL, "id", $program_id );
			
			$rval = self::custom(
				$prec->starttime,
				$prec->endtime,
				$prec->channel_id,
				$prec->title,
				$prec->description,
				$prec->category_id,
				$program_id,
				$autorec,
				$mode );
				
		}
		catch( Exception $e ) {
			throw $e;
		}
		return $rval;
	}
	
	public static function custom(
		$starttime,				// 開始時間Datetime型
		$endtime,				// 終了時間Datetime型
		$channel_id,			// チャンネルID
		$title = "none",		// タイトル
		$description = "none",	// 概要
		$category_id = 0,		// カテゴリID
		$program_id = 0,		// 番組ID
		$autorec = 0,			// 自動録画
		$mode = 0				// 録画モード
	) {
		global $RECORD_MODE;
		
		// 時間を計算
		$start_time = toTimestamp( $starttime );
		$end_time = toTimestamp( $endtime );
		
		if( $start_time < (time() + PADDING_TIME + 10) ) {	// 現在時刻より3分先より小さい＝すでに開始されている番組
			$start_time = time() + PADDING_TIME + 10;		// 録画開始時間を3分10秒先に設定する
		}
		$at_start = $start_time - PADDING_TIME;
		$sleep_time = PADDING_TIME - FORMER_TIME;
		$rec_start = $start_time - FORMER_TIME;
		
		// durationを計算しておく
		$duration = $end_time - $rec_start;
		if( $duration < (FORMER_TIME + 60) ) {	// 60秒以下の番組は弾く
			throw new Exception( "終わりつつある/終わっている番組です" );
		}
		
		$rrec = null;
		try {
			// 同一番組予約チェック
			if( $program_id ) {
				$num = DBRecord::countRecords( TBL_PREFIX.RESERVE_TBL, "WHERE program_id = '".$program_id."'" );
				if( $num ) {
					throw new Exception("同一の番組が録画予約されています");
				}
			}
			
			$crec = new DBRecord( TBL_PREFIX.CHANNEL_TBL, "id", $channel_id );
			
			// 既存予約数 = TUNER番号
			$tuners = ($crec->type == "GR") ? GR_TUNERS : BS_TUNERS;
			$battings = DBRecord::countRecords( TBL_PREFIX.RESERVE_TBL, "WHERE complete = '0' ".
																	  "AND type = '".$crec->type."' ".
																	  "AND starttime < '".toDatetime($end_time) ."' ".
																	  "AND endtime > '".toDatetime($rec_start)."'"
			);
			
			if( $battings >= $tuners ) {
				// 重複を発見した
				if( FORCE_CONT_REC ) {
					// 解消可能な重複かどうかを調べる
					// 前後の予約数
					$nexts = DBRecord::countRecords( TBL_PREFIX.RESERVE_TBL, "WHERE complete = '0' ".
																		"AND type = '".$crec->type."' ".
																		"AND starttime = '".toDatetime($end_time - FORMER_TIME)."'");
					
					$prevs = DBRecord::countRecords( TBL_PREFIX.RESERVE_TBL, "WHERE complete = '0' ".
																		"AND type = '".$crec->type."' ".
																		"AND endtime = '".$starttime."'"  );
					
					// 前後を引いてもチューナー数と同数以上なら重複の解消は無理
					if( ($battings - $nexts - $prevs) >= $tuners )
						throw new Exception( "重複予約を解消できません" );
					
					// 直後の番組はあるか?
					if( $nexts ) {
						// この番組の終わりをちょっとだけ早める
						$end_time = $end_time - FORMER_TIME - REC_SWITCH_TIME;
						$duration = $end_time - $rec_start;		// durationを計算しなおす
					}
					$battings -= $nexts;
					
					// 直前の録画予約を見付ける
					$trecs = DBRecord::createRecords(TBL_PREFIX.RESERVE_TBL, "WHERE complete = '0' ".
																			 "AND type = '".$crec->type."' ".
																			 "AND endtime = '".$starttime."'" );
					// 直前の番組をずらす
					for( $i = 0; $i < count($trecs) ; $i++ ) {
						if( $battings < $tuners ) break;	// 解消終了のハズ?
						// 予約修正に必要な情報を取り出す
						$prev_id           = $trecs[$i]->id;
						$prev_program_id   = $trecs[$i]->program_id;
						$prev_channel_id   = $trecs[$i]->channel_id;
						$prev_title        = $trecs[$i]->title;
						$prev_description  = $trecs[$i]->description;
						$prev_category_id  = $trecs[$i]->category_id;
						$prev_starttime    = $trecs[$i]->starttime;
						$prev_endtime      = $trecs[$i]->endtime;
						$prev_autorec      = $trecs[$i]->autorec;
						$prev_mode         = $trecs[$i]->mode;
						
						$prev_start_time = toTimestamp($prev_starttime);
						// 始まっていない予約？
						if( $prev_start_time > (time() + PADDING_TIME + FORMER_TIME) ) {
							// 開始時刻を元に戻す
							$prev_starttime = toDatetime( $prev_start_time + FORMER_TIME );
							// 終わりをちょっとだけずらす
							$prev_endtime   = toDatetime( toTimestamp($prev_endtime) - FORMER_TIME - REC_SWITCH_TIME );
							
							// tryのネスト
							try {
								// いったん予約取り消し
								self::cancel( $prev_id );
								// 再予約
								self::custom( 
									$prev_starttime,			// 開始時間Datetime型
									$prev_endtime,				// 終了時間Datetime型
									$prev_channel_id,			// チャンネルID
									$prev_title,				// タイトル
									$prev_description,			// 概要
									$prev_category_id,			// カテゴリID
									$prev_program_id,			// 番組ID
									$prev_autorec,				// 自動録画
									$prev_mode );
							}
							catch( Exception $e ) {
								throw new Exception( "重複予約を解消できません" );
							}
						}
						else {
							throw new Exception( "重複予約を解消できません" );
						}
						$battings--;
					}
					if( $battings < 0 ) $battings = 0;
					// これで重複解消したはず
				}
				else {
					throw new Exception( "重複予約があります" );
				}
			}
			// チューナー番号
			$tuner = $battings;
			
			// 改めてdurationをチェックしなおす
			if( $duration < (FORMER_TIME + 60) ) {	// 60秒以下の番組は弾く
				throw new Exception( "終わりつつある/終わっている番組です" );
			}
			
			$filename = "".$crec->type.$crec->channel."_".date("YmdHis", $start_time)."_".date("YmdHis", $end_time).$RECORD_MODE[$mode]['suffix'];
			
			// 予約レコードを埋める
			$rrec = new DBRecord( TBL_PREFIX.RESERVE_TBL );
			$rrec->channel_disc = $crec->channel_disc;
			$rrec->channel_id = $crec->id;
			$rrec->program_id = $program_id;
			$rrec->type = $crec->type;
			$rrec->channel = $crec->channel;
			$rrec->title = $title;
			$rrec->description = $description;
			$rrec->category_id = $category_id;
			$rrec->starttime = toDatetime( $rec_start );
			$rrec->endtime = toDatetime( $end_time );
			$rrec->path = $filename;
			$rrec->autorec = $autorec;
			$rrec->mode = $mode;
			$rrec->reserve_disc = md5( $crec->channel_disc . toDatetime( $start_time ). toDatetime( $end_time ) );
			
			// 予約実行
			$cmdline = AT." ".date("H:i m/d/Y", $at_start);
			$descriptor = array( 0 => array( "pipe", "r" ),
			                     1 => array( "pipe", "w" ),
			                     2 => array( "pipe", "w" ),
			);
			$env = array( "CHANNEL"  => $crec->channel,
				          "DURATION" => $duration,
				          "OUTPUT"   => INSTALL_PATH.SPOOL."/".$filename,
				          "TYPE"     => $crec->type,
			              "TUNER"    => $tuner,
			              "MODE"     => $mode,
			);
			
			// ATで予約する
			$process = proc_open( $cmdline , $descriptor, $pipes, SPOOL, $env );
			if( is_resource( $process ) ) {
				fwrite($pipes[0], SLEEP." ".$sleep_time."\n" );
				fwrite($pipes[0], DO_RECORD . "\n" );
				fwrite($pipes[0], COMPLETE_CMD." ".$rrec->id."\n" );
				if( USE_THUMBS ) {
					// サムネール生成
					$ffmpeg_cmd = FFMPEG." -i \${OUTPUT} -r 1 -s 160x90 -ss ".(FORMER_TIME+2)." -vframes 1 -f image2 ".INSTALL_PATH.THUMBS."/".$filename.".jpg\n";
					fwrite($pipes[0], $ffmpeg_cmd );
				}
				fclose($pipes[0]);
				// 標準エラーを取る
				$rstring = stream_get_contents( $pipes[2]);
				
			    fclose( $pipes[2] );
			    proc_close( $process );
			}
			else {
				$rrec->delete();
				throw new Exception("AT実行エラー");
			}
			// job番号を取り出す
			$rarr = array();
			$tok = strtok( $rstring, " \n" );
			while( $tok !== false ) {
				array_push( $rarr, $tok );
				$tok = strtok( " \n" );
			}
			$key = array_search("job", $rarr);
			if( $key !== false ) {
				if( is_numeric( $rarr[$key+1]) ) {
					$rrec->job = $rarr[$key+1];
					return $rrec->job;			// 成功
				}
			}
			// エラー
			$rrec->delete();
			throw new Exception( "job番号の取得に失敗" );
		}
		catch( Exception $e ) {
			if( $rrec != null ) {
				if( $rrec->id ) {
					// 予約を取り消す
					$rrec->delete();
				}
			}
			throw $e;
		}
	}
	// custom 終了
	
	// 取り消し
	public static function cancel( $reserve_id = 0, $program_id = 0 ) {
		$rec = null;
		
		try {
			if( $reserve_id ) {
				$rec = new DBRecord( TBL_PREFIX.RESERVE_TBL, "id" , $reserve_id );
			}
			else if( $program_id ) {
				$rec = new DBRecord( TBL_PREFIX.RESERVE_TBL, "program_id" , $program_id );
			}
			if( $rec == null ) {
				throw new Exception("IDの指定が無効です");
			}
			if( ! $rec->complete ) {
				// 未実行の予約である
				if( toTimestamp($rec->starttime) < (time() + PADDING_TIME + FORMER_TIME) )
					throw new Exception("過去の録画予約です");
				exec( ATRM . " " . $rec->job );
			}
			$rec->delete();
		}
		catch( Exception $e ) {
			throw $e;
		}
	}
}
?>