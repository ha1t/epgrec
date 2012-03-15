<?php
/**
 * update_crontab.php
 *
 * 予約情報をcrontabにおとす
 *
 * simpleReservationが実行された時だけ実行すればよさそうに見えるが、
 * crontabをapacheから書き換える事はできないので、cronから書き換える必要がある
 */
mb_language("ja");
define('ROOT_DIR', dirname(__FILE__) . '/');
define('APP_DIR', ROOT_DIR . '/app/');

require_once ROOT_DIR.'dietcake/dietcake.php';

require_once dirname(__FILE__) . '/config.php';

// keywordから予約対象を調べて予約する
Keyword::reserveAll();

// 一旦TMPに出す。
file_put_contents('/tmp/crontab.txt', RecorderService::generateCrontab());
exec('crontab /tmp/crontab.txt');
