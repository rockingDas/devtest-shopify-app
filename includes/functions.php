<?php


function verifyHmac($params, $secret) {
    if (!isset($params['hmac'])) return false;
    $hmac = $params['hmac'];
    unset($params['hmac']);

    ksort($params);
    $computed = hash_hmac('sha256', http_build_query($params), $secret);
    return hash_equals($hmac, $computed);
}


function error_show(){
    
}
