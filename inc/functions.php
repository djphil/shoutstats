<?php
function debug($variable)
{
    echo '<pre>'.print_r($variable, true).'</pre>';
}

function socket_get_7html($ip, $port, $sid)
{
    Global $failed;
    Global $timeout;
    $result = array();
    $socket = @fsockopen($ip, $port, $errno, $errstr, $timeout);
    if (!$socket) {$failed++; return $result;}
    fputs($socket, "GET /7.html".$sid." HTTP/1.0\r\nUser-Agent: Mozilla\r\n\r\n");
    while (!feof($socket)) {$csv = fgets($socket);}
    $result = explode(',', strip_tags($csv));
    if (empty($result[1])) {$failed++; return $result;} 
    return $result;
}
?>
