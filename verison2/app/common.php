<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/3
 * Time: 23:38
 */
define('WEEK',18); //定义学期教学总周数
/**
 * 生成一个GUID值,38位字符
 * @param null
 * @return string  {65034FBB-0526-F03D-5262-5C4314ABD0A7}
 */
function getGUID($sessionId){
    $guid = md5(time().$sessionId);
    $guid = "{".substr($guid,0,4).trim(chunk_split(substr($guid,4,-4),4, "-"),"-").substr($guid,-4)."}";
    return strtoupper($guid);
}
/**获取学年学期
 * @return mixed
 */
function get_year_term(){
    $year=date('Y',time());
    $month=date('m',time());
    if($month>=2&&$month<=8){
        $workyear['term']=2;
        $workyear['year']=$year-1;
    }
    elseif($month<2){
        $workyear['term']=1;
        $workyear['year']=$year-1;
    }
    else
    {
        $workyear['term']=1;
        $workyear['year']=$year;
    }
    return $workyear;
}
function get_next_year_term(){
    $year=date('Y',time());
    $month=date('m',time());
    if($month>=2&&$month<=8){
        $workyear['term']=1;
        $workyear['year']=$year;
    }
    elseif($month<2){
        $workyear['term']=2;
        $workyear['year']=$year-1;
    }
    else
    {
        $workyear['term']=2;
        $workyear['year']=$year;
    }
    return $workyear;
}
/**二进制反序
 * @param $string
 * @return string
 */
function bin_reserve($string){
    return join('',array_reverse(str_split($string)));
}

/**将周次转为二进制，不足位补零
 * @param $decimal
 * @param int $week 周次数
 * @return string
 */
function week_dec2bin($decimal,$week=18){
    return str_pad(decbin($decimal),$week,'0',STR_PAD_LEFT);
}

/** 将周次转为二进制，不足位补零, 并反序
 * @param $decimal
 * @param int $week
 * @return string
 */

function week_dec2bin_reserve($decimal,$week=18){
    return bin_reserve(str_pad(decbin($decimal),$week,'0',STR_PAD_LEFT));
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0,$adv=false) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if($adv){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}
