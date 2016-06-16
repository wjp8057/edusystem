<?php
/**
 * 课程表.
 * User: educk
 * Date: 13-12-20
 * Time: 上午9:41
 */
class TimetableAction extends RightAction {
    private $model;

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }

    /**
     * 班课表
     */
    public function myClassTime(){
        $year = intval($_REQUEST["YEAR"]);
        $term = intval($_REQUEST["TERM"]);
        if($year==0 || $term==0){
            $data = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
            $year = $data['YEAR'];
            $term = $data['TERM'];
        }
        $userInfo = session("S_USER_INFO");
        $bind = $this->model->getBind("YEAR,TERM,CLASSNO",array($year, $term, $userInfo["CLASSNO"]));
        $data = $this->model->sqlQuery($this->model->getSqlMap("timeTable/getClass.sql"),$bind);

        $this->assign("list",$this->getTimeTable($data));
        $this->assign("YEAR",$year);
        $this->assign("TERM", $term);
        $this->display();
    }

    /**
 * 周课表
 */
    public function myWeekTime(){
        $year = intval($_REQUEST["YEAR"]);
        $term = intval($_REQUEST["TERM"]);
        if($year==0 || $term==0){
            $data = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
            $year = $data['YEAR'];
            $term = $data['TERM'];
        }
        $studentno= session("S_USER_NAME");
        $bind = $this->model->getBind("YEAR,TERM",array($year, $term,));
        $data = $this->model->sqlQuery("SELECT SCHEDULE.TIME+SCHEDULE.DAY+SCHEDULE.OEW AS TIME,
RTRIM(COURSES.COURSENAME)+':'+SCHEDULE.COURSENO+SCHEDULE.[GROUP]+'('+RTRIM(CLASSROOMS.JSN)+')'+TEACHERS.NAME AS COURSE,
SCHEDULE.WEEKS,
TASKOPTIONS.NAME AS TASK
FROM SCHEDULE JOIN R32
ON SCHEDULE.COURSENO=R32.COURSENO
AND SCHEDULE.[GROUP]=R32.[GROUP]
AND SCHEDULE.YEAR=R32.YEAR
AND SCHEDULE.TERM=R32.TERM
JOIN COURSES
ON SCHEDULE.COURSENO=COURSES.COURSENO
JOIN CLASSROOMS ON SCHEDULE.ROOMNO=CLASSROOMS.ROOMNO
JOIN TEACHERPLAN ON SCHEDULE.MAP=TEACHERPLAN.RECNO
JOIN TEACHERS ON TEACHERPLAN.TEACHERNO=TEACHERS.TEACHERNO
LEFT OUTER JOIN TASKOPTIONS ON TEACHERPLAN.TASK=TASKOPTIONS.CODE
WHERE SCHEDULE.YEAR=:YEAR
AND SCHEDULE.TERM=:TERM
AND R32.STUDENTNO='{$studentno}'
ORDER BY TIME",$bind);

        $this->assign("list",$this->getTimeTable($data));
        $this->assign("YEAR",$year);
        $this->assign("TERM", $term);
        $this->display();
    }

    private function getTimeTable($data, $rowspan=2){
        $list = array();
        if(!count($data)) return $list;

        $timeData = $this->model->sqlQuery("select NAME,VALUE,UNIT,TIMEBITS from TIMESECTORS");
        //所有课时列表以NAME为索引
        $timesectors = array_reduce($timeData, "myTimesectorsReduce");
        //取得单节课时自然数为索引
        $countTimesectors = array_reduce($timeData, "myCountTimesectors");
        //单双周
        $both = array("B"=>"","E"=>"（双周）","O"=>"（单周）");

        foreach($data as $row){
            $list = $this->makeTime($list,$row,$rowspan, $both, $timesectors,$countTimesectors);
        }
        return $list;
    }

    private function makeTime($list, $times, $rowspan, $both, $timesectors, $countTimesectors){
        $list = (array)$list;
        $split = str_split($times["TIME"]);
        if($split[0]=='0'){
            $list[0] .= $times["COURSE"]."<br/>";
            return $list;
        }

        $_time = $timesectors[$split[0]];
        for($i=1;$i<count($countTimesectors); $i+=$rowspan){
            //现在是以双节排
            for($j=0; $j<$rowspan; $j++){
                //说明有课跳出循环
                if(($timesectors[$countTimesectors[$i-1+$j]]['TIMEBITS'] & $_time['TIMEBITS'])>0){
                    $weeks='';
                    if($times['WEEKS']!=262143){
                        $weeks=' 周次 '.$this->colorr($times['WEEKS']);
                    }
                    $list[($i-1)/$rowspan+1][$split[1]] .= ($timesectors[$split[0]]['UNIT']=="1" ? '('.trim($timesectors[$split[0]]['VALUE']).')' : '').$both[$split[2]].$times["COURSE"].$weeks."<br/>";
                    break;
                }
            }
        }
        return $list;
    }
    public function colorr($str2){
        $aa=str_pad(strrev(decbin($str2)),18,0);
        $str='';
        $str.='<font color="blue">'.substr($aa,0,4).'</font>&nbsp';
        $str.='<font color="#222">'.substr($aa,4,4).'</font>&nbsp';
        $str.='<font color="green">'.substr($aa,8,4).'</font>&nbsp';
        $str.='<font color="red">'.substr($aa,12,4).'</font>&nbsp';
        $str.='<font color="black">'.substr($aa,16,4).'</font>&nbsp';
        return $str;
    }
}

function myTimesectorsReduce($v1, $v2){
    if(!$v1) $v1 = array();
    $v1[$v2["NAME"]] = $v2;
    return $v1;
}
function myCountTimesectors($v1, $v2){
    if(!$v1) $v1 = array();
    if($v2['UNIT']=="1") $v1[]=$v2["NAME"];
    return $v1;
}