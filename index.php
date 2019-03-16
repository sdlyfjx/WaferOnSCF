<?php
/**
 * Created by PhpStorm.
 * User: ayisun
 * Date: 2016/10/2
 * Time: 10:54
 */
header('Cache-Control:no-cache,must-revalidate');    
header('Pragma:no-cache');
require_once('system/parse_request.php');
date_default_timezone_set("PRC");

function main_handler($event, $context) {
    // print_r($event);
    // print_r($event->body);
    $parse_request = new parse_request();
    $return_result = $parse_request->parse_json($event->body);
    print($return_result);
    return $return_result;
}