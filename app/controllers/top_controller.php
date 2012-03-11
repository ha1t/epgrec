<?php
/**
 *
 *
 */
class TopController extends AppController
{
    public function index() 
    {
        require_once ROOT_DIR . '/config.php';
        require_once INSTALL_PATH . "/Settings.class.php";

        // 設定ファイルの有無を検査する
        if (!file_exists(INSTALL_PATH . "/settings/config.xml")) {
            header( "Content-Type: text/html;charset=utf-8" );
            exit( "<script type=\"text/javascript\">\n" .
                "<!--\n".
                "window.open(\"install/step1.php\",\"_self\");".
                "// -->\n</script>" );
        }

        $settings = Settings::factory();

        $DAY_OF_WEEK = array( "(日)","(月)","(火)","(水)","(木)","(金)","(土)" );

        // パラメータの処理
        // 表示する長さ（時間）
        $program_length = $settings->program_length;
        if (isset($_GET['length'])) {
           $program_length = (int)$_GET['length'];
        }

        // 地上=GR/BS=BS
        $type = "GR";

        // 現在の時間
        $now_time = mktime( date("H"), 0 , 0 );
        if (isset($_GET['time'] ) ) {
            if( sscanf( $_GET['time'] , "%04d%2d%2d%2d", $y, $mon, $day, $h ) == 4 ) {
                $tmp_time = mktime( $h, 0, 0, $mon, $day, $y );
                if( ($tmp_time < ($now_time + 3600 * 24 * 8)) && ($tmp_time > ($now_time - 3600 * 24 * 8)) )
                    $now_time = $tmp_time;
            }
        }
        $last_time = $now_time + 3600 * $program_length;

        // 時刻欄
        $tv_hours = array(); 
        for ($i = 0; $i < $program_length; $i++) {
            $tv_hours[] = date("H", $now_time + 3600 * $i);
        }

        $channel_map = ChannelMaster::$GR;

        $st = 0;
        $programs = array();
        foreach ($channel_map as $channel_disc => $no_use) {
            $prev_end = $now_time;

            $channel = Channel::get($channel_disc);
            $options = array(
                'channel_disc' => $channel_disc,
                'endtime' => date('Y-m-d H:i:s', $now_time),
                'starttime' => date('Y-m-d H:i:s', $last_time),
            );
            $rows = Program::getPrograms($options);
            $programs[$st]["station_name"]  = $channel->name;
            $programs[$st]["channel_disc"]  = $channel->channel_disc;
            $programs[$st]['list'] = array();
            /*
                'category_none' => 'none',
                'height' => 0,
                'title' => '',
                'starttime' => '',
                'description' => '',
                'duration' => '',
            );
             */
            $num = 0;
            foreach ($rows as $row) {
                // 前プログラムとの空きを調べる
                $start = strtotime($row['starttime']);

                if (($start - $prev_end) > 0) {
                    $height = ($start - $prev_end) * $settings->height_per_hour / 3600;
                    if (isset($programs[$st]['list'][$num])) {
                        throw new Exception('ここはNULLでなければいけません');
                    } 
                    $programs[$st]['list'][$num] = array(
                        'category_name' => 'none',
                        'height' => $height,
                        'title' => '',
                        'starttime' => '',
                        'description' => '',
                        'duration' => '',
                        'program_disc' => '',
                        'prg_start' => '',
                        'channel' => '',
                        'rec' => 0,
                    );
                    $num++;
                }
                $prev_end = strtotime($row['endtime']);

                $height = ((strtotime($row['endtime']) - strtotime($row['starttime'])) * $settings->height_per_hour / 3600);
                // $now_time より早く始まっている番組
                if (strtotime($row['starttime']) < $now_time) {
                    $height = ((strtotime($row['endtime']) - $now_time ) * $settings->height_per_hour / 3600);
                }
                // $last_time より遅く終わる番組
                if (strtotime($row['endtime']) > $last_time) {
                    $height = (($last_time - strtotime($row['starttime'])) * $settings->height_per_hour / 3600);
                }

                // プログラムを埋める
                $category = Category::get($row['category_disc']);
                if ($category === false) {
                    $category_name = 'none';
                }

                $programs[$st]['list'][$num]['category_name'] = $category->name_en;
                $programs[$st]['list'][$num]['program_disc'] = $row['program_disc'];
                $programs[$st]['list'][$num]['height'] = $height;
                $programs[$st]['list'][$num]['title'] = $row['title'];
                $programs[$st]['list'][$num]['starttime'] = date("H:i", $start )."" ;
                $programs[$st]['list'][$num]['description'] = $row['description'];
                $programs[$st]['list'][$num]['prg_start'] = str_replace( "-", "/", $row['starttime']);
                $programs[$st]['list'][$num]['duration'] = "" . (strtotime($row['endtime']) - strtotime($row['starttime']));
                $programs[$st]['list'][$num]['channel'] = ($row['type'] == "GR" ? "地上D" : "BS" ) . ":". $row['channel'] . "ch";
                if (Reserve::get($row['program_disc'])) {
                    $programs[$st]['list'][$num]['rec'] = 1;
                } else {
                    $programs[$st]['list'][$num]['rec'] = 0;
                }
                $num++;
            }

            // 空きを埋める
            if (($last_time - $prev_end) > 0) {
                if (isset($programs[$st]['list'][$num])) {
                    throw new Exception('NULLである必要があります'); 
                } 
                $programs[$st]['list'][$num] = array(
                    'category_name' => 'none',
                    'height' => ($last_time - $prev_end) * $settings->height_per_hour / 3600,
                    'title' => '',
                    'starttime' => '',
                    'description' => '',
                    'duration' => '',
                    'program_disc' => '',
                    'prg_start' => '',
                    'channel' => '',
                    'rec' => 0,
                );
                $num++;
            }
            $st++;
        }

        // 局の幅
        $ch_set_width = $settings->ch_set_width;
        // 全体の幅
        $chs_width = $ch_set_width * count($channel_map);

        // GETパラメタ
        $get_param = $_SERVER['SCRIPT_NAME'] . "?type=GR&length=".$program_length."";

        $categories = Category::getAll(); // カテゴリ一覧

        // タイプ選択
        $types = array();
        if ($settings->gr_tuners != 0) {
            $tuner_type = array(
                'selected' => $type == "GR" ? 'class="selected"' : "",
                'link' => $_SERVER['SCRIPT_NAME'] . "?type=GR&length=".$program_length."&time=".date( "YmdH", $now_time),
                'name' => '地上デジタル'
            );
            $types[] = $tuner_type;
        }

        // 日付選択
        $days = array();
        $day = array();
        $day['d'] = "昨日";
        $day['link'] = $get_param . "&time=". date( "YmdH", time() - 3600 *24 );
        $day['ofweek'] = "";
        $day['selected'] = $now_time < mktime( 0, 0 , 0) ? 'class="selected"' : '';

        array_push( $days , $day );
        $day['d'] = "現在";
        $day['link'] = $get_param;
        $day['ofweek'] = "";
        $day['selected'] = "";
        array_push( $days, $day );
        for( $i = 0 ; $i < 8 ; $i++ ) {
            $day['d'] = "".date("d", time() + 24 * 3600 * $i ) . "日";
            $day['link'] = $get_param . "&time=".date( "Ymd", time() + 24 * 3600 * $i) . date("H" , $now_time );
            $day['ofweek'] = $DAY_OF_WEEK[(int)date( "w", time() + 24 * 3600 * $i )];
            $day['selected'] = date("d", $now_time) == date("d", time() + 24 * 3600 * $i ) ? 'class="selected"' : '';
            array_push( $days, $day );
        }

        // 時間選択
        $toptimes = array();
        for( $i = 0 ; $i < 24; $i+=4 ) {
            $tmp = array();
            $tmp['hour'] = sprintf( "%02d:00", $i );
            $tmp['link'] = $get_param . "&time=".date("Ymd", $now_time ) . sprintf("%02d", $i );
            array_push( $toptimes, $tmp );
        }
        $ch_set_width    = $settings->ch_set_width;
        $height_per_hour = $settings->height_per_hour;
        $height_per_min  = $settings->height_per_hour / 60;
        $sitetitle = date( "Y", $now_time ) . "年" . date( "m", $now_time ) . "月" . date( "d", $now_time ) . "日". date( "H", $now_time ) .
            "時～地上デジタル番組表";
        $now_time  = str_replace( "-", "/" ,date('Y-m-d H:i:s', $now_time));
        $last_time = str_replace( "-", "/" ,date('Y-m-d H:i:s', $last_time));

        /*
        $smarty = new Smarty();
        $smarty->template_dir = ROOT_DIR . 'templates/'; 
        $smarty->compile_dir = ROOT_DIR . 'templates_c/'; 
        $smarty->assign(get_defined_vars());
        $smarty->display('index.html');
        exit;
         */

        $this->set(get_defined_vars()); 
    }
}
