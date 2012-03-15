#!/usr/bin/php
<?php
/**
 * 番組表を取得する。
 */
define('ROOT_DIR', dirname(__FILE__) . '/');
define('APP_DIR', ROOT_DIR . '/app/');

require_once ROOT_DIR.'dietcake/dietcake.php';

require_once dirname(__FILE__) . '/config.php';
require_once INSTALL_PATH . '/Settings.class.php';

$settings = Settings::factory();

if (file_exists($settings->temp_data)) {
    unlink($settings->temp_data);
}

// 地上波を処理する
if ($settings->gr_tuners != 0) {
    foreach (ChannelMaster::$GR as $key => $channel_no) {
        // 録画重複チェック
        $db = DB::conn();
        $row = $db->row("SELECT COUNT(*) FROM Recorder_reserveTbl LEFT JOIN Recorder_programTbl ON Recorder_reserveTbl.program_disc = Recorder_programTbl.program_disc WHERE complete = '0' AND type = 'GR' AND endtime > NOW() AND starttime < addtime( NOW(), '00:01:10')");
        if (is_array($row) && current($row) == 0) {
            $temp_filename = str_replace('.ts', "_GR{$channel_no}.ts", $settings->temp_data);

            // 直近1時間のtsない時だけ作る
            if (!RecorderService::isDumped($temp_filename)) {
                $options = array(
                    'CHANNEL' => $channel_no,
                    'DURATION' => 30,
                    'TYPE' => 'GR',
                    'TUNER' => 0,
                    'MODE' => 0,
                    'OUTPUT' => $temp_filename,
                );
                RecorderService::doRecord($options);
            }

            // dump
            $xml = str_replace('.xml', "_GR{$channel_no}.xml", $settings->temp_xml);
            $cmdline = "{$settings->epgdump} {$key} {$temp_filename} {$xml}";
            exec($cmdline);

            // parse
            if (file_exists($xml)) {
                RecorderService::storeProgram('GR', $xml);
                RecorderService::cleanup();
                // unlink($xml);
            }
        }
    }
}
