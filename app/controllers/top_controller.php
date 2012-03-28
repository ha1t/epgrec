<?php
/**
 *
 *
 */
class TopController extends AppController
{
    // TODO: program/indexに持っていく
    public function index()
    {
        require_once ROOT_DIR . '/config.php';
        require_once INSTALL_PATH . "/Settings.class.php";

        // 設定ファイルの有無を検査する
        if (!file_exists(INSTALL_PATH . "/settings/config.xml")) {
            $this->redirect('install/step1');
        }

        $settings = Settings::factory();
        $type = "GR"; // 地上=GR/BS=BS

        // 表示する長さ（時間）
        $program_length = (int)Param::get('length', $settings->program_length);

        // 現在の時間
        $now_time = mktime( date("H"), 0 , 0 );
        if (isset($_GET['time'])) {
            if (sscanf($_GET['time'], "%04d%2d%2d%2d", $y, $mon, $day, $h) == 4) {
                $tmp_time = mktime( $h, 0, 0, $mon, $day, $y );
                if (($tmp_time < ($now_time + 3600 * 24 * 8)) && ($tmp_time > ($now_time - 3600 * 24 * 8))) {
                    $now_time = $tmp_time;
                }
            }
        }
        $last_time = $now_time + 3600 * $program_length;

        // 時刻欄
        $tv_hours = array();
        for ($i = 0; $i < $program_length; $i++) {
            $tv_hours[] = date("H", $now_time + 3600 * $i);
        }

        $st = 0;
        $programs = array();
        foreach (ChannelMaster::$GR as $channel_disc => $no_use) {
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
                if (Reserve::get($row['program_disc'])) {
                    $row['rec'] = 1;
                } else {
                    $row['rec'] = 0;
                }
                $category = Category::get($row['category_disc']);
                if ($category === false) {
                    $row['category_name'] = 'none';
                }
                $row['category_name'] = $category->name_en;
                $row['height'] = $height;
                $row['starttime'] = date("Y-m-d H:i", $start);
                $row['prg_start'] = str_replace( "-", "/", $row['starttime']);
                $row['duration'] = strtotime($row['endtime']) - strtotime($row['starttime']);
                $row['channel'] = ($row['type'] == "GR" ? "地上D" : "BS" ) . ":". $row['channel'] . "ch";

                $programs[$st]['list'][$num] = $row;
                $num++;
            }

            // 高さだけを持つ空のアイテムを作って入れる
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

        $ch_set_width = $settings->ch_set_width; // 局の幅
        $chs_width = $ch_set_width * count(ChannelMaster::$GR); // 全体の幅
        $base_url = url('top/index')  . "?type=GR&length={$program_length}";
        $categories = Category::getAll(); // カテゴリ一覧

        // タイプ選択
        $types = array();
        if ($settings->gr_tuners != 0) {
            $tuner_type = array(
                'selected' => $type == "GR" ? 'class="selected"' : "",
                'link' => $_SERVER['SCRIPT_NAME'] . "?type=GR&length=".$program_length."&time=" . date("YmdH", $now_time),
                'name' => '地上デジタル'
            );
            $types[] = $tuner_type;
        }

        // 日付選択
        $days[] = array(
            'd' => "昨日",
            'link' => "{$base_url}&time=". date('YmdH', time() - 3600 * 24),
            'ofweek' => 7,
            'selected' => $now_time < mktime(0, 0 ,0) ? true : false,
        );
        $days[] = array(
            'd' => '現在',
            'link' => $base_url,
            'ofweek' => 7,
            'selected' => false,
        );
        for ($i = 0; $i < 8; $i++) {
            $day = array(
                'd' => date('d', time() + 24 * 3600 * $i) . '日',
                'link' => "{$base_url}&time=" . date('Ymd', time() + 24 * 3600 * $i) . date("H" , $now_time),
                'ofweek' => (int)date("w", time() + 24 * 3600 * $i),
                'selected' => date("d", $now_time) == date("d", time() + 24 * 3600 * $i ) ? true : false,
            );
            $days[] = $day;
        }

        $ch_set_width    = $settings->ch_set_width;
        $height_per_hour = $settings->height_per_hour;
        $height_per_min  = $settings->height_per_hour / 60;
        $sitetitle = date('Y年m月d日H時～地上デジタル番組表', $now_time);
        $now_time  = str_replace('-', '/' ,date('Y-m-d H:i:s', $now_time));
        $last_time = str_replace('-', '/' ,date('Y-m-d H:i:s', $last_time));

        $this->set(get_defined_vars());
    }
}
