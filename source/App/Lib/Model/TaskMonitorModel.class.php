<?php
/**
 * 任务运行器
 * User: cwebs
 * Date: 2015/5/27
 * Time: 9:20
 */
class TaskMonitorModel{
    private static $KEY_MSG = "___MSG___";

    /**
     * 初始化一个任务监控
     * array("R"=>"运行","T"=>"总数","C"=>"当前执行数","I"=>"已读书取的消息","ST"=>"开始时间","CT"=>"当前时间","YES"=>"成功","NO"=>"失败","MSG"=>"任务主消息");
     *
     * @param $key 缓存唯一主键，一般为session("S_USER_NAME")
     * @param $title 任务标题
     */
    public static function init($key, $title){
        TaskMonitorModel::autoClear();
        if(TaskMonitorModel::exists($key)) TaskMonitorModel::stop($key);

        TaskMonitorModel::set($key, array("TITLE"=>$title,"R"=>true,"T"=>0,"C"=>0, "ST"=>time(), "CT"=>time(),"YES"=>0,"NO"=>0,"MSG"=>$title."初始化工作..."));
        TaskMonitorModel::appendMsg($key, time(), true, $title."初始化工作...");
    }

    /**
     * 运行一个任务
     *
     * @param $key 缓存唯一主键，一般为session("S_USER_NAME")
     * @param $title 任务标题
     * @param $total 任务总数
     */
    public static function run($key, $title, $total){
        if(!TaskMonitorModel::exists($key)) TaskMonitorModel::init($key, $title);

        $taskInfo = TaskMonitorModel::get($key);
        $taskInfo["T"] = $total;
        $taskInfo["CT"] = time();
        $taskInfo["MSG"] = "任务开始循环执行...";
        TaskMonitorModel::set($key, $taskInfo);
    }

    /**
     * 设置任务主消息
     *
     * @param $key 缓存唯一主键，一般为session("S_USER_NAME")
     * @param $msg 任务主消息
     */
    public static function setInfoMsg($key, $msg){
        $taskInfo = TaskMonitorModel::get($key);
        $taskInfo["CT"] = time();
        $taskInfo["MSG"] = $msg;
        TaskMonitorModel::set($key, $taskInfo);
    }
    
    /**
     * 记录运行任务中的一个消息
     * @param $key 缓存唯一主键，一般为session("S_USER_NAME")
     * @param $current 当前执行的任务号
     * @param $status 执行成功或者失败
     * @param $message 当前一个任务的消息,为null时不记录消息到消息缓存区
     * @param $sleepTime 执行完成后停止毫秒数
     */
    public static function next($key, $current, $status, $message=null, $sleepTime=0){
        $taskInfo = TaskMonitorModel::get($key);
        $taskInfo["C"] = $current;
        $taskInfo["CT"] = time();
        if($status) $taskInfo["YES"]++;
        else $taskInfo["NO"]++;
        TaskMonitorModel::set($key, $taskInfo);

        //todo 如果消息存在则保存到消息缓存区
        if($message!==null) TaskMonitorModel::appendMsg($key, $taskInfo["CT"], $status, $message);

        //todo 如果停止时间大于０则停止程序再执行
        if($sleepTime>0) usleep($sleepTime * 1000);
    }

    /**
     * 设置完成
     * @param $key 缓存唯一主键，一般为session("S_USER_NAME")
     */
    public static function done($key){
        usleep(3000 * 1000);
        $taskInfo = TaskMonitorModel::get($key);
        $taskInfo["R"] = "done";
        TaskMonitorModel::set($key, $taskInfo);

        TaskMonitorModel::appendMsg($key, time(), true, "执行完成！");
    }

    /**
     * 停止运行，$key为空时消除所有任务
     * @param null $key
     */
    public static function stop($key=null){
        if(empty($key)) TaskMonitorModel::clear();
        else {
            TaskMonitorModel::delete($key);
            TaskMonitorModel::delete($key.TaskMonitorModel::$KEY_MSG);
        }
    }

    /**
     * 获得运行任务信息，每次读取都会清空消息缓存区
     * @param $key 缓存唯一主键，一般为session("S_USER_NAME")
     * @return array
     */
    public static function info($key,$clientTime){
        $msg = TaskMonitorModel::flush($key.TaskMonitorModel::$KEY_MSG);
        $taskInfo = TaskMonitorModel::get($key);

        //todo 如果没有客户端时间，或者小于当前传入时间
        if(!empty($taskInfo) && $taskInfo['CLIENTTIME']!=$clientTime) {
            if($clientTime>$taskInfo['ST']) $clientTime = $taskInfo['ST']-3;

            $taskInfo['CLIENTTIME'] = $clientTime;
            $taskInfo['R'] = true;
            TaskMonitorModel::set($key, $taskInfo);
        }
        //if($taskInfo["R"]=="done") TaskMonitorModel::stop($key);

        return array($taskInfo, $msg);
    }

    /**
     * 追加消息到消息缓存区
     * @param $key 缓存唯一主键，一般为session("S_USER_NAME")
     * @param $ct 消息时间
     * @param $status 执行是否成功
     * @param $message 消息
     */
    public static function appendMsg($key, $ct, $status, $message){
        $msg = TaskMonitorModel::get($key.TaskMonitorModel::$KEY_MSG);
        if(empty($msg)) $msg = array();

        $msg[] = array("CT"=>$ct,"S"=>$status,"MSG"=>$message);
        TaskMonitorModel::set($key.TaskMonitorModel::$KEY_MSG, $msg);
    }










    /**
     * 设置全局值
     * @param $key
     * @param $value
     */
    public static function set($key, $value){
        wincache_ucache_set($key, serialize($value));
    }

    /**
     * 获得全局值
     * @param $key
     * @return mixed
     */
    public static function get($key){
        return unserialize(wincache_ucache_get($key));
    }

    /**
     * 删除全局值
     * @param $key
     * @return bool
     */
    public static function delete($key){
        return wincache_ucache_delete($key);
    }

    /**
     * 清除所有全局值
     * @return bool
     */
    public static function clear(){
        return wincache_ucache_clear();
    }

    /**
     * 当内存使用大于1/3时自动进行垃圾搜集
     */
    public static function autoClear(){
        $info = wincache_ucache_meminfo();
        if($info['memory_free'] / $info['memory_total'] < 0.5){
            TaskMonitorModel::clear();
        }
    }

    /**
     * 指定KEY的全局值是否存在
     * @param $key
     * @return bool
     */
    public static function exists($key){
        return wincache_ucache_exists($key);
    }

    /**
     * 刷出指定$key的缓存区
     * @param $key
     * @return mixed
     */
    public static function flush($key){
        $data = wincache_ucache_get($key);
        if($data==false) return;

        TaskMonitorModel::delete($key);
        return unserialize($data);
    }
}