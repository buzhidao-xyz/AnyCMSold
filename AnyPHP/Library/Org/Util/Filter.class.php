<?php
/**
 * 过滤工具
 * 2015-05-23
 * imbzd
 */
namespace Org\Util;

class Filter
{
    //初始化
    public function __construct(){}

    //过滤规则：字母开始、字母数字_-.组合、长度5-20
    static public function F_CharNoShort($var=null)
    {
        $regexp = "/[a-zA-Z][a-zA-Z0-9_-.]{4,19}/i";
        if (preg_match($regexp, $var) == 0) {
            return false;
        }

        return true;
    }
}