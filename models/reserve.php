<?php
/**
 *
 *
 */
class Reserve
{
    const TABLE = 'Recorder_reserveTbl';

    // @TODO getでfalseみるだけじゃないの
    public function isReserved($program_disc) {
        $db = DB::conn();
        $result = $db->row('SELECT * FROM ' . self::TABLE . ' WHERE program_disc = ?', array($program_disc));
        if ($result !== false) {
            return true;
        } else {
            return false;
        }
    }

    public static function getRecordedItems($options = array())
    {
        $now = date("Y-m-d");
        $db = DB::conn();
        $table = self::TABLE;
        $program_table  = Program::TABLE;
        $category_table = Category::TABLE;
        $channel_table  = Channel::TABLE;
        $sql = <<<EOD
SELECT * FROM {$table}
  INNER JOIN {$program_table} ON {$table}.program_disc = {$program_table}.program_disc
  LEFT JOIN {$category_table} ON {$category_table}.category_disc = {$program_table}.category_disc
  LEFT JOIN {$channel_table} ON {$channel_table}.channel_disc = {$program_table}.channel_disc
WHERE endtime < "{$now}"
ORDER BY starttime DESC
EOD;
        return $db->rows($sql);
    }

    // @TODO 書きなおす
    public static function getReservations()
    {
        $now = date("Y-m-d");
        $db = DB::conn();
        $table = self::TABLE;
        $program_table = Program::TABLE;
        $category_table = Category::TABLE;
        $sql = <<<EOD
SELECT * FROM {$table}
  INNER JOIN {$program_table} ON {$table}.program_disc = {$program_table}.program_disc
  LEFT JOIN {$category_table} ON {$program_table}.category_disc = {$category_table}.category_disc
WHERE complete = 0 AND starttime > "{$now}"
ORDER BY starttime ASC
EOD;
        return $db->rows($sql);
    }

    // @TODO 同一番組をすでに予約している場合警告
    // @TODO 同時間帯に別のチャンネルを予約している場合に警告
    public static function simpleReserve($program_disc) {
        $db = DB::conn();
        $program = Program::get($program_disc);
        $row = array(
            'program_disc' => $program->program_disc,
            'autorec' => 0,
            'mode' => 0,
            'job' => 0,
        );
        return $db->replace(self::TABLE, $row);
    }
}
