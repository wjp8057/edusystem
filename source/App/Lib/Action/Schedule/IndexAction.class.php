<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 14-5-23
 * Time: 下午12:00
 */
class IndexAction extends RightAction {
    private $model;
    protected $message = array("type"=>"info","message"=>"","dbError"=>"");

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }

    public function index(){
        $this->assign("yearTerm",$this->model->sqlFind("select * from YEAR_TERM where [TYPE]='O'"));
        $this->__done("index");
    }

    /**
     * 自动创建课程总表
     */
    public function auto(){
        if(VarIsIntval("YEAR,TERM")==false){
            $this->message["type"] = "error";
            $this->message["message"] = "缺少必要的参数，非法提交！";
            $this->__done();
        }

        $bind = $this->model->getBind("YEAR,TERM", $_REQUEST);
//        var_dump($_POST['checked']);
        if(trim($_POST['checked']) == '1'){
            $this->model->sqlExecute("DELETE SCHEDULE WHERE YEAR=:YEAR AND TERM=:TERM", $bind);
        }
        $data = $this->model->sqlQuery($this->model->getSqlMap("Schedule/autoSchedule.sql"), $bind);
        if($data==null){
            $this->message["type"] = "info";
            $this->message["message"] = "没有任何排课计划需要导入！";
            $this->__done();
        }

        $sql = "INSERT INTO SCHEDULE (YEAR,TERM,COURSENO,[GROUP],OEW,WEEKS,LE,MAP,UNIT,TIMER,ROOMR,EMPROOM) VALUES "
                ."(:YEAR,:TERM,:COURSENO,:GROUP,:OEW,:WEEKS,:TASK,:RECNO,:UNIT,:TIME,:ROOMTYPE,:EMPROOM)";
        foreach($data as $row){
            $max = floor($row["HOURS"]/$row["UNIT"]);
            for($i=0;$i<$max;$i++){
                $bind = $this->model->getBind("YEAR,TERM,COURSENO,GROUP,OEW,WEEKS,TASK,RECNO,UNIT,TIME,ROOMTYPE,EMPROOM", $row);
                $bind[":OEW"] = "B";
//                $ret1 = $this->model->sqlExecute($sql, $bind);
                if(!$this->model->sqlExecute($sql, $bind)){
                    $this->message["type"] = "info";
                    $this->message["message"] = "第 $i 条记录插入失败";
                    $this->__done("auto");
                }
            }
            $mod = $row["HOURS"]-$row["UNIT"]*$max;
            if($mod==0) continue;
            elseif($mod*2>$row["UNIT"]) $oew = "E";
            else $oew = "O";

            trace($row["HOURS"]."%".$row["UNIT"]."=".$mod."(".$oew.")");

            $bind = $this->model->getBind("YEAR,TERM,COURSENO,GROUP,OEW,WEEKS,TASK,RECNO,UNIT,TIME,ROOMTYPE,EMPROOM", $row);
            $bind[":OEW"] = $oew;
            //echo $this->model->sqlExecute($sql, $bind);
            if(!$this->model->sqlExecute($sql, $bind)){
                $this->message["type"] = "info";
                $this->message["message"] = "插入失败";
                $this->__done("auto");
            }
        }

        //todo 设置教师已传递到排课表
        //$bind = $this->model->getBind("YEAR,TERM", $_REQUEST);
        //$this->model->sqlExecute("UPDATE SCHEDULEPLAN SET TOSCHEDULE=1 WHERE YEAR=:YEAR AND TERM=:TERM AND TEACHERNO is not null AND TEACHERNO<>'' AND <>'000000'", $bind);
        $this->message["type"] = "info";
        $this->message["message"] = $_REQUEST["YEAR"]."年".$_REQUEST["TERM"]."学期排课计划成功导入！".count($data).$this->model->getDbError();
        $this->__done("auto");
    }


    public function refreshResTime2(){
        if($this->_hasJson){
            $str='';
            $num=0;
            $day=array(1=>0,0,0,0,0,0,0);

            //todo:找出每个教师上的课程
            $sql = $this->model->getSqlMap('schedule/source_teacherCourse.SQL');
            $bind = array(':syear'=>$_POST['YEAR'],':sterm'=>$_POST['TERM'],':tyear'=>$_POST['YEAR'],':tterm'=>$_POST['TERM']);
            $courseList=$this->model->sqlQuery($sql,$bind);
            $courseList2=$this->groupday($courseList);
            foreach($courseList as $v){
                if($v['TIMES']&$day[$v['DAY']]){       //todo:某一天的某节课和该天的课 &
                    foreach($courseList2[$v['DAY']] as $v2){
                        if($v2['TIMES']&$v['TIMES']&&$v2['WEEKS']&$v['WEEKS']&&$v['row']!=$v2['row']){
                            $str.=$num.':错误:教师'.$v['TEACHERNO'].',课程:'.$v['COURSE'].',在星期:'.$v['DAY'].',时段:'.$v['jc'].',单双周:'.$v['dsz'].','.'周次:'.str_pad(strrev(decbin($v['WEEKS'])),18,0).'<br>';
                            $num++;
                            break;
                        }
                    }
                }
                $day[$v['DAY']]|=$v['TIMES'];
            }
            if(!$num){
                $str = "<h1 style='text-align: center'>刷新完成！</h1>";
            }
            echo json_encode(array('str'=>$str,'count'=>$num));
            exit;
        }

        $this->assign('year',$_GET['YEAR']);
        $this->assign('term',$_GET['TERM']);
        $this->display();
    }
    /**
     *刷新资源时间表
     */
    public function refreshResTime(){
        $this->display();
    }


    //todo:判断班级时间
    public function classTime(){
        $str='';
        $num=$_POST['NUM'];
        //todo:找出每个班级上的课程
        $courseList=$this->model->sqlQuery($this->model->getSqlMap('schedule/source_classCourse.SQL'),
            array(':syear'=>$_POST['YEAR'],':sterm'=>$_POST['TERM'],':tyear'=>$_POST['YEAR'],':tterm'=>$_POST['TERM']));
        $courseList2=$this->groupday($courseList);
        $day=array();
        foreach($courseList as $v){
            if($v['TIMES']&$day[$v['DAY']]){       //todo:某一天的某节课和该天的课 &
                foreach($courseList2[$v['DAY']] as $v2){
                    if($v2['TIMES']&$v['TIMES']&&$v2['WEEKS']&$v['WEEKS']&&$v['row']!=$v2['row']){
                        $str.=$num.':错误:班级'.$v['CLASSNO'].',课程:'.$v['COURSE'].',在星期:'.$v['DAY'].',时段:'.$v['jc'].',单双周:'.$v['dsz'].','.'周次:'.str_pad(strrev(decbin($v['WEEKS'])),18,0).'<br>';
                        $num++;
                        break;
                    }
                }
            }
            $day[$v['DAY']]|=$v['TIMES'];
        }
        echo $str;
    }

    //todo:判断课程是否冲突
    public function is_Repeat(){
        $courseList=$this->model->sqlQuery("SELECT (COURSENO+[GROUP]) as courseno,SCHEDULE.DAY,SCHEDULE.TIME,SCHEDULE.OEW,WEEKS,OEWOPTIONS.TIMEBIT&TIMESECTIONS.TIMEBITS2 AS TIMES
             FROM SCHEDULE
            inner join TIMESECTIONS on TIMESECTIONS.NAME=SCHEDULE.TIME
            inner join OEWOPTIONS on OEWOPTIONS.CODE=SCHEDULE.OEW
            WHERE SCHEDULE.YEAR=:YEAR AND SCHEDULE.TERM=:TERM
            ORDER BY courseno",
        array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));
        $arr=array();
      /*  echo '<pre>';
        print_r($courseList);*/
        foreach($courseList as $val){
                    if(!is_array($arr[$val['courseno']])){
                        $arr[$val['courseno']]=array();
                    }
                 array_push($arr[$val['courseno']],$val);
        }
        $str='';
       ;
        foreach($arr as $val){
            if(count($val)>1){
                $time=array(1=>array('TIMES'=>0),array('TIMES'=>0),array('TIMES'=>0),array('TIMES'=>0),array('TIMES'=>0),array('TIMES'=>0),array('TIMES'=>0));
                foreach($val as $v){
                    if($v['TIMES']&$time[$v['DAY']]['TIMES']){
                        $str.=':错误:课程'.$val['courseno'].'<br>';
                    }
                    $time[$v['DAY']]['TIMES']|=$v['TIMES'];
                }
            }
        }
        echo $str;
    }



    public function groupday($arr){
        $array=array(1=>array(),2=>array(),3=>array(),4=>array(),5=>array(),6=>array(),7=>array());
        foreach($arr as $val){
            array_push($array[$val['DAY']],$val);
        }
        return $array;
    }

    /**
     *锁定
     */
    public function lock(){
        if($this->_hasJson){
            if(VarIsIntval("YEAR,TERM")==false){
                $this->message["type"] = "error";
                $this->message["message"] = "提交的参数不正确，非法提交！";
                $this->__done();
            }
            $bind = $this->model->getBind("YEAR,TERM",$_REQUEST);
            $sql = "UPDATE SCHEDULE SET LOCK=1 where YEAR=:YEAR and TERM=:TERM and DAY<>'0'";
            $data = $this->model->sqlExecute($sql,$bind);
            if($data===false){
                $this->message["type"] = "error";
                $this->message["message"] = "<font color='red'>加锁时发生错误，加锁失败！</font>";
            }else{
                $this->message["type"] = "info";
                $this->message["message"] = $_REQUEST['YEAR']."年第".$_REQUEST['TERM']."学期排定的课表成功加锁！";
            }
            $this->__done();
        }
        $this->__done("lock");
    }

    /**
     *解锁
     */
    public function unlock(){
        if($this->_hasJson){
            if(VarIsIntval("YEAR,TERM")==false){
                $this->message["type"] = "error";
                $this->message["message"] = "提交的参数不正确，非法提交！";
                $this->__done();
            }
            $bind = $this->model->getBind("YEAR,TERM",$_REQUEST);
            $sql = "UPDATE SCHEDULE SET LOCK=0 where YEAR=:YEAR and TERM=:TERM";
            $data = $this->model->sqlExecute($sql,$bind);
            if($data===false){
                $this->message["type"] = "error";
                $this->message["message"] = "<font color='red'>解锁时发生错误，解锁失败！</font>";
            }else{
                $this->message["type"] = "info";
                $this->message["message"] = $_REQUEST['YEAR']."年第".$_REQUEST['TERM']."学期排定的课表封锁解除！";
            }
            $this->__done();
        }
        $this->__done("unlock");
    }

    /**
     * 设定资源竞争状态
     */
    public function setResStatus(){
         if($this->_hasJson){
             if(VarIsIntval("SCHEDULERESOURCE")==false){
                 $this->message["type"] = "error";
                 $this->message["message"] = "提交的参数不正确，非法提交！";
                 $this->__done();
             }
             $bind = $this->model->getBind("STATE",$_REQUEST['SCHEDULERESOURCE']==1?1:0);
             $sql = "UPDATE STATES SET STATE=:STATE WHERE STATENAME='SCHEDULERESOURCE'";
             $data = $this->model->sqlExecute($sql,$bind);
             if($data===false){
                 $this->message["type"] = "error";
                 $this->message["message"] = "<font color='red'>设定资源竞争状态发生错误，设定失败！</font>";
             }else{
                 $this->message["type"] = "info";
                 $this->message["message"] = "资源竞争状态已成功设定！";
             }
             $this->__done();
         }
        $data = $this->model->sqlCount($this->model->getSqlMap("Schedule/queryScheduleState.sql"));
        $this->assign("ScheduleResource",$data);
        $this->__done("setresstatus");
    }
}