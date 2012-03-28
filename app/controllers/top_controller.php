<?php

require_once ROOT_DIR . '/config.php';
require_once INSTALL_PATH . "/Settings.class.php";

class TopController extends AppController
{
    // TODO: program/indexに持っていく
    public function index()
    {
        // 設定ファイルの有無を検査する
        if (!file_exists(INSTALL_PATH . "/settings/config.xml")) {
            $this->redirect('install/step1');
        }

        $settings = Settings::factory();

        // 表示する長さ（時間）
        $program_length = (int)Param::get('length', $settings->program_length);

        // 現在の時間
        $now_time = $this->getNowTime();
        $last_time = $now_time + 3600 * $program_length;

        $programs = self::getChannelPrograms($now_time, $last_time);

        $base_url = url('top/index', array('type' => 'GR', 'length' => $program_length));

        // 日付選択
        $days[] = array(
            'd' => "昨日",
            'link' => url('top/index', array('type' => 'GR', 'length' => $program_length, 'time' => date('YmdH', time() - 3600 * 24))),
            'ofweek' => 7,
            'selected' => $now_time < mktime(0, 0 ,0) ? true : false,
        );
        $days[] = array(
            'd' => '現在',
            'link' => url('top/index', array('type' => 'GR', 'length' => $program_length)),
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
        $chs_width       = $ch_set_width * count(ChannelMaster::$GR); // 全体の幅
        $height_per_hour = $settings->height_per_hour;
        $height_per_min  = $settings->height_per_hour / 60;
        $sitetitle = date('Y年m月d日H時～地上デジタル番組表', $now_time);
        $categories = Category::getAll(); // カテゴリ一覧

        $this->set(get_defined_vars());
    }

    private static function getChannelPrograms($now_time, $last_time)
    {
        $settings = Settings::factory();
        $channel_programs = array();
        foreach (ChannelMaster::$GR as $channel_disc => $no_use) {
            $prev_end = $now_time;

            $channel = Channel::get($channel_disc);
            $channel_program = array(
                "name" => $channel->name,
                "channel_disc" => $channel->channel_disc,
                'programs' => array()
            );

            $options = array(
                'channel_disc' => $channel_disc,
                'endtime' => date('Y-m-d H:i:s', $now_time),
                'starttime' => date('Y-m-d H:i:s', $last_time),
            );
            $programs = Program::getPrograms($options);

            foreach ($programs as $program) {
                // 前プログラムとの空きを調べる
                $start = strtotime($program->starttime);

                // 放送開始前の状態を検出して空のprogramを作っている?
                if (($start - $prev_end) > 0) {
                    $height = ($start - $prev_end) * $settings->height_per_hour / 3600;
                    $channel_program['programs'][] = array(
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
                }

                $prev_end = strtotime($program->endtime);

                $height = ((strtotime($program->endtime) - strtotime($program->starttime)) * $settings->height_per_hour / 3600);
                // $now_time より早く始まっている番組
                if (strtotime($program->starttime) < $now_time) {
                    $height = ((strtotime($program->endtime) - $now_time) * $settings->height_per_hour / 3600);
                }
                // $last_time より遅く終わる番組
                if (strtotime($program->endtime) > $last_time) {
                    $height = (($last_time - strtotime($program->starttime)) * $settings->height_per_hour / 3600);
                }

                // プログラムを埋める
                if (Reserve::get($program->program_disc)) {
                    $program->rec = 1;
                } else {
                    $program->rec = 0;
                }
                $category = Category::get($program->category_disc);
                if ($category === false) {
                    $program->category_name = 'none';
                }
                $program->category_name = $category->name_en;
                $program->height = $height;
                $program->starttime = date("Y-m-d H:i", $start);
                $program->prg_start = str_replace( "-", "/", $program->starttime);
                $program->duration = strtotime($program->endtime) - strtotime($program->starttime);
                $program->channel = ($program->type == "GR" ? "地上D" : "BS" ) . ":". $program->channel . "ch";

                $channel_program['programs'][] = (array) $program;
            }

            // 放送時間終了対策で高さだけを持つ空のアイテムを作って入れる
            if (($last_time - $prev_end) > 0) {
                $channel_program['programs'][] = array(
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
            }

            $channel_programs[] = $channel_program;
        }

        return $channel_programs;
    }

    // 現在の時間
    private function getNowTime()
    {
        $time = Param::get('time', false);
        $now_time = mktime(date("H"), 0, 0);
        if ($time && sscanf($time, "%04d%2d%2d%2d", $y, $mon, $day, $h) == 4) {
            $tmp_time = mktime($h, 0, 0, $mon, $day, $y);
            if (($tmp_time < ($now_time + 3600 * 24 * 8)) && ($tmp_time > ($now_time - 3600 * 24 * 8))) {
                $now_time = $tmp_time;
            }
        }
        return $now_time;
    }
}
