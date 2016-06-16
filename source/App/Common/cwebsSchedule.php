<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 14-6-10
 * Time: 上午9:02
 */
class cwebsSchedule {
    public static $oewName = array("B"=>"单双","E"=>"双周","O"=>"单周"); //单双周名称
    public static $oewVal = array("B"=>3,"E"=>2,"O"=>1); //单双周值
    const totalLessonLen = 13; //总课程节数为13节
    const totalLessonMap = 67108863; //总课程节数表，从右到左第2个位代表一节课,现在是13节课11111111111111111111111111
    public static $unitMap = array(1=>"单节课","双节课","三节课","四节课");
    public static $weekMap = array("未按排","MON"=>"星期一","TUE"=>"星期二","WES"=>"星期三","THU"=>"星期四","FRI"=>"星期五","SAT"=>"星期六","SUN"=>"星期天");
    public static $timesMap = array(1=>8191,4095,array(112,1792),4095);
    public static $conflictItem = array("ROOMNO","CLASSNO","TEACHNO","COURSENO"); //冲突检测项

    public function __construct(){

    }

    /**
     * 前补0操作
     * @param $str 字符
     * @param int $len 需要的长度
     * @param int $zero 补值
     * @return string
     */
    public static function zero($str,$len=0,$zero=0){
        if($len<1 || $len<strlen($str)) return $str;
        return sprintf("%".$zero.$len."s", $str);
    }

    /**
     * 把上课节次与单双周匹配，得到周次时间表
     * @param $lesson 上次节次时间表如：第1、2节为3
     * @param $oewType 单双周
     * @return number
     */
    public static function lesson2Time($lesson, $oewType){
        $bin = str_split(decbin($lesson),1);
        foreach($bin as $k=>$v){
            $bin[$k] = cwebsSchedule::zero(decbin(bindec($v.$v) & cwebsSchedule::$oewVal[$oewType]),2);
        }
        return bindec(implode("",$bin));
    }

    /**
     * 检测是否冲突
     * @param $lesson
     * @param $oewType
     * @param $data
     * @return bool
     */
    public static function conflict($lesson, $oewType, $data){
        if(!$data["ORTIMES"] || $data["INITTIMES"]==$data["TIMES"]) return false;

        $time = cwebsSchedule::lesson2Time($lesson, $oewType);
        $ortime = intval($data["ORTIMES"]);
        $xortime = intval($data["XORTIMES"]);
        $_initTime = intval($data["INITTIMES"]);

        if($xortime>0) $_initTime = (($xortime ^ cwebsSchedule::totalLessonMap) & $_initTime);
        if(($time & $ortime)>0 && ($_initTime & $ortime)>0) return true;
        else return false;
    }
}