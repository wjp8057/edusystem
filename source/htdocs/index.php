<?php
/**
 * 主程序入口页
 * User: educk
 * Date: 13-11-20
 * Time: 下午12:54
 */


//定义项目名称
define('APP_NAME', 'App');
//定义项目路径
define('APP_PATH', '../App/');
//开启调试模式
define('APP_DEBUG',true);
//URL模式
define("URL_MODEL", 1);
//打印参数详细信息
function mist(){
    $params = func_get_args();
    //随机浅色背景
    $str='9ABCDEF';
    $color='#';
    for($i=0;$i<6;$i++) {
        $color=$color.$str[rand(0,strlen($str)-1)];
    }
    //传入空的字符串或者==false的值时 打印文件
    $traces = debug_backtrace();
    $title = "<b>File:</b>{$traces[0]['file']} << <b>Line:</b>{$traces[0]['line']} >> ";
    echo "<pre style='background: {$color};width: 100%;'><h3 style='color: midnightblue'>{$title}</h3>";
    foreach ($params as $key=>$val){
        echo '<b>Param '.$key.':</b><br />'.var_export($val, true).'<br />';
    }
    echo '</pre>';
    exit;
}
function mistey(){
    header("Content-type:text/html;charset=utf-8");
    $params = func_get_args();
    //随机浅色背景
    $str='9ABCDEF';
    $color='#';
    for($i=0;$i<6;$i++) {
        $color=$color.$str[rand(0,strlen($str)-1)];
    }
    //传入空的字符串或者==false的值时 打印文件
    $traces = debug_backtrace();
    $title = "<b>File:</b>{$traces[0]['file']} << <b>Line:</b>{$traces[0]['line']} >> ";
    echo "<pre style='background: {$color};width: 100%;'><h3 style='color: midnightblue'>{$title}</h3>";
    foreach ($params as $key=>$val){
        echo '<b>Param '.$key.':</b><br />'.var_export($val, true).'<br />';
    }
    echo '</pre>';
}

//存放sql Map的路径，建议放到服务器要目录以外
define("SQL_MAP_PATH","../sqlMap/");

//加载框架入口文件
require '../ThinkPHP/ThinkPHP.php';
