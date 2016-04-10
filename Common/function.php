<?php
//格式化标准时间
function mkDateTime($time=0, $his=1)
{
    !$time ? $time = TIMESTAMP : null;

    $format = $his ? 'Y-m-d H:i:s' : 'Y-m-d';
    return date($format, $time);
}

//生成guid 8-4-4-4-12
function guid(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    } else {
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8).$hyphen
              .substr($charid, 8, 4).$hyphen
              .substr($charid,12, 4).$hyphen
              .substr($charid,16, 4).$hyphen
              .substr($charid,20,12);
        return $uuid;
    }
}