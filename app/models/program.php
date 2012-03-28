<?php
/**
 *
 *
 */
class Program extends AppModel
{
    const TABLE = 'Recorder_programTbl';

    public static function get($program_disc)
    {
        $db = DB::conn();
        $table = self::TABLE;
        $row = $db->row("SELECT * FROM {$table} WHERE program_disc = ?", array($program_disc));
        if ($row === false) {
            return false;
        }
        return new self($row);
    }

    public function update(array $row)
    {
        $db = DB::conn();
        return $db->update(self::TABLE, $row, array('program_disc' => $this->program_disc));
    }

    public static function search($options, $args)
    {
        $db = DB::conn();
        $table = self::TABLE;
        $category_table = Category::TABLE;
        $sql = <<<__SQL__
SELECT * FROM {$table}
  LEFT JOIN {$category_table} ON {$table}.category_disc = {$category_table}.category_disc
  {$options}
__SQL__;
        return $db->rows($sql, $args);
    }

    public static function disableAutorec($program_disc)
    {
        $db = DB::conn();
        return $db->update(self::TABLE, array('autorec' => 0), array('program_disc' => $program_disc));
    }

    // TODO: チャンネルごとのプログラムなのでメソッド名を修正する
    public function getPrograms($options = array()) 
    {
        $db = DB::conn();
        $table = self::TABLE;
        $sql = <<<__SQL__
SELECT * FROM {$table}
  WHERE channel_disc = :channel_disc AND endtime > :endtime AND starttime < :starttime
  ORDER BY starttime ASC
__SQL__;
        $rows = $db->rows($sql, $options);

        if (is_array($rows)) {
            foreach ($rows as $key => $row) {
                $rows[$key] = new self($row);
            }
        }

        return $rows;
    }
}
