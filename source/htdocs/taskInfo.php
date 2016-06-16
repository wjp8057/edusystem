<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 2015/6/1
 * Time: 16:02
 */
error_reporting(E_ERROR);
include_once "../App/Lib/Model/TaskMonitorModel.class.php";

if(trim($_REQUEST["key"])) $key = trim($_REQUEST["key"]);
else{
    @header('HTTP/1.1 404 Not Found');
    @header("status: 404 Not Found");
    exit;
}

$data = TaskMonitorModel::info($key, $_REQUEST['clientTime']);
header('Content-Type:application/json; charset=utf-8');
exit(json_encode($data));