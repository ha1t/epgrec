<?php
class Log
{
    /**
     * ログメッセージを debug.log ファイルに出力する
     *
     * 開発環境で動作します。
     * 本番環境でこのメソッドが呼ばれても何も起きません。
     *
     * 開発中に、変数のダンプをするときなどに使います。
     *
     * @param mixed $msg ログメッセージ
     * @return void
     */
    public static function debug($msg)
    {
        if (ENV_PRODUCTION) {
            return;
        }

        self::write(LOGS_DIR.'debug.log', $msg);
    }

    /**
     * ログメッセージを info.log ファイルに出力する
     *
     * 全ての環境で動作します。
     *
     * 本番環境上でデバッグを行うときや、
     * 何らかの情報を記録しておきたいときに使います。
     *
     * @param mixed $msg ログメッセージ
     * @return void
     */
    public static function info($msg)
    {
        self::write(LOGS_DIR.'info.log', $msg);
    }

    /**
     * ログメッセージを warn.log ファイルに出力する
     *
     * 全ての環境で動作します。
     *
     * 開発者がいち早く知るべき問題が発生したときに使います。
     * 例えば、決済関連の API が失敗して例外が発生したときなどです。
     *
     * このメソッドでは、warn.log ファイルに出力することしかしないため、
     * システム側で warn.log ファイルを監視して通知をする仕組みを整えてください。
     *
     * @param mixed $msg ログメッセージ
     * @return void
     */
    public static function warn($msg)
    {
        self::write(LOGS_DIR.'warn.log', $msg);
    }

    /**
     * 指定されたファイルにログを書き込む
     *
     * @param string $file 対象のファイル
     * @param mixed $msg ログメッセージ
     * @return void
     */
    protected static function write($file, $msg)
    {
        $player_id = Param::get('opensocial_viewer_id');

        $msg = self::convertVariableToString($msg);

        // マイクロ秒オーダーの時間を取得
        list($u_sec, $sec) = explode(' ', microtime());
        $date = sprintf("%s.%06d%s", date('Y-m-d\TH:i:s', $sec), (int)round($u_sec * 1000000), date('P'));

        $log = sprintf("%s\t%s\t%s\n", $date, $player_id, $msg);
        file_put_contents($file, $log, FILE_APPEND | LOCK_EX);
    }

    /**
     * 文字列以外のデータをログ出力のために文字列に変換する
     *
     * @param mixed 文字列に変換するデータ
     * @return string
     */
    protected static function convertVariableToString($data)
    {
        if (is_string($data)) {
            return $data;
        }
        ob_start();
        ini_set('html_errors', 0);
        var_dump($data);
        ini_set('html_errors', 1);
        return ob_get_clean();
    }
}
