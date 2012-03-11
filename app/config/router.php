<?php
function url($url = '', $params = array())
{
    $query = http_build_query($params);
    if (strlen($query) > 0) {
        $query = '?' . $query;
    }
    if ($url === '') {
        $url = substr($_SERVER['REQUEST_URI'], strlen(APP_URL));
        $r = APP_URL . $url;
    } elseif ($url === '/') {
        $r = APP_URL . $query;
    } else {
        $r = APP_URL . $url . $query;
    }
    return $r;
}
