<?php

function getProxy()
{
    global $proxy_url;


    try {

        $proxy_info = json_decode(fetch($proxy_url), true);

        $type = '';

        if (empty($proxy_info['protocol'])) {
            return false;
        }

        switch ($proxy_info['protocol']) {
            case 'http':
                $type = CURLPROXY_HTTP;
                break;
            case 'socks4':
                $type = CURLPROXY_SOCKS4;
                break;
            case 'socks4a':
                $type = CURLPROXY_SOCKS4A;
                break;
            case 'socks5':
                $type = CURLPROXY_SOCKS5;
                break;
        }

        if (empty($type)) {
            sleep(rand(1, 3));
            return getProxy();
        }

        $proxy_info['type'] = $type;


        $proxy_info['full'] = $proxy_info['ip'] . ':' . $proxy_info['port'];

        return $proxy_info;

    } catch (Exception $exception) {
        return false;
    }
}


function getProxyList()
{
    global $proxy_list;
    $url = 'https://www.proxy-list.download/api/v1/get?type=http';

    $data = fetch($url);

    $proxy_list = preg_split('/\n/m', $data);

    return $proxy_list;
}


function updateAgent()
{
    global $cookiePath1, $def_proxy_info;


//        $def_proxy_info = getProxy();
    $proxy_list = getProxyList();
    $index = rand(0, count($proxy_list) - 1);
    $def_proxy_info['full'] = $proxy_list[$index];
    $def_proxy_info['type'] = CURLPROXY_HTTP;
    @unlink($cookiePath1);

    return true;
}

function fetch($url, $z = null)
{
    global $cookiePath, $def_proxy_info;

    $result = '';
    try {
        $ch = curl_init();

        if (!empty($z['params'])) {
            $url .= '?' . http_build_query($z['params']);
        }

        $useragent = isset($z['useragent']) ? $z['useragent'] : 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:10.0.2) Gecko/20100101 Firefox/10.0.2';

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 200); // http request timeout 20 seconds

        if (!empty($def_proxy_info)) {
            curl_setopt($ch, CURLOPT_PROXYTYPE, $def_proxy_info['type']);
            curl_setopt($ch, CURLOPT_PROXY, $def_proxy_info['full']);
//        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
        }


//
//        if (isset($z['post'])) {
//            curl_setopt($ch, CURLOPT_POST, true);
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $z['post']);
//        }

        if (isset($z['refer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $z['refer']);
        }

        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (isset($z['timeout']) ? $z['timeout'] : 5));
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiePath);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiePath);

        //https://stackoverflow.com/questions/8419747/php-curl-does-not-work-on-localhost
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


        $result = curl_exec($ch);

        curl_close($ch);
    } catch (Exception $ex) {

    }

    usleep(rand(210304, 5503146));

    return $result;
}


/**
 * Получить столбцы таблицы
 * @param $table_name string Исходная таблица
 * @return array Список столбцов
 */
function getColumnNames($table_name)
{
    global $db;
    $columns = array();

    try {
        $sql = "SHOW COLUMNS FROM `$table_name`";
        $result = $db->query($sql);
        while ($row = $db->fetch($result)) {
            $columns[] = $row['Field'];
        }
    } catch (Exception $ex) {

    }
    return $columns;
}


function clearCookie()
{
    global $cookiePath;
    file_put_contents($cookiePath, '');
    return true;
}

function checkRegular($re, $str, $index = 1)
{
    $result = '';
    $matches = array();

    if (preg_match($re, $str, $matches)) {
        if (!empty($matches[$index])) {
            $result = $matches[$index];
        }
    }
    return $result;
}

function checkArrayFilled($array)
{
    foreach ($array as $key => $value) {
        if (empty($array[$key])) {
            return false;
        }
    }
    return true;
}

function jsRandom()
{
    return mt_rand() / (mt_getrandmax() + 1);
}


function delApostrof($string)
{
    $bad_symbol = '"';
    $count = substr_count($string, $bad_symbol);
    $last_symbol = substr($string, -1);


    if ($count % 2 == 1 && $last_symbol == $bad_symbol) {
        $string = substr($string, 0, -1);
    }
    return $string;
}

function echoVarDumpPre($var)
{
    echo '<pre>';
//    echo json_encode($var);
    var_dump($var);
    echo '</pre>';
    exit;
}


