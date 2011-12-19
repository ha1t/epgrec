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
        $db = DB::conn();
        $table = self::TABLE;
        $program_table  = Program::TABLE;
        $category_table = Category::TABLE;
        $channel_table  = Channel::TABLE;
        $sql = <<<EOD
SELECT * FROM {$table}
  LEFT JOIN {$program_table} ON {$table}.program_disc = {$program_table}.program_disc
  LEFT JOIN {$category_table} ON {$category_table}.category_disc = {$program_table}.category_disc
  LEFT JOIN {$channel_table} ON {$channel_table}.channel_disc = {$program_table}.channel_disc
ORDER BY starttime DESC
EOD;
        return $db->rows($sql);
    }
}
