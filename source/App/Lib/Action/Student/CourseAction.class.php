<?php
/**
 * 选课、退课模块
 * User: educk
 * Date: 13-12-25
 * Time: 下午2:59
 */
class CourseAction extends RightAction {
    private $model;
    private $_array = array(1=>"MON","TUE","WES","THU","FRI","SAT","SUN");

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }

    /**
     * [ACT]查询界面
     */
    public function query(){
        $this->assign("isKaopingLock",$this->isKaopingLock(session("S_USER_NAME")));

        $this->assign("isFee",$this->isFee(session("S_USER_NAME")));
        $this->display();
    }

    /**
     * [ACT]选课列表
     */
    public function qlist(){
        if($this->_hasJson){
            $bind = $this->model->getBind("YEAR,TERM,COURSENOGROUP,COURSENAME,TEACHERNAME,COURSETYPE,SCHOOL,CLASSNO,DAY,TIME", $_REQUEST, "%");
            $data = $this->model->sqlFind($this->model->getSqlMap("course/countStudentQuerySchedule.sql"), $bind);
            $json["total"] = $data['ROWS'];

            if($json["total"]>0){
                $sql = $this->model->getPageSql(null,"course/studentQuerySchedule.sql", $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }else $json["rows"] = array();
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->assign("queryParams",count($_REQUEST)>0?json_encode($_REQUEST):"{}");
        $this->display();
    }

    /**
     * [ACT]选课确认
     */
    public function selected(){
        //todo: 传递回来的课程号为ids数组
        //定义全局消息变量
        $message = array("type"=>"info","message"=>"","dbError"=>"");

        //todo: 0、传入值检测
        //检测学年，学期是否正确
        if(!isset($_REQUEST["YEAR"]) || $_REQUEST["YEAR"]<2000 || !isset($_REQUEST["TERM"]) || $_REQUEST["TERM"]==0 || $_REQUEST["TERM"]>4){
            $message["type"] = "error";
            $message["message"] = "指定的学年和学期有误，请检查！";
            $this->ajaxReturn($message,"JSON");
            exit;
        }
        elseif(!isset($_REQUEST["ids"])){
            $message["type"] = "error";
            $message["message"] = "没有选择任一课程！";
            $this->ajaxReturn($message,"JSON");
            exit;
        }

        //todo: 1、检测是否已缴费
        if($this->isFee(session("S_USER_NAME"))==0){
            $message["type"] = "error";
            $message["message"] = "您还没有交纳学费，不能进行选课！";
            $this->ajaxReturn($message,"JSON");
            exit;
        }

        //todo: 2、对比学生ids数组中的选择课程是否有冲突
        /**
        if(($message = $this->checkStudentSchedule($_REQUEST["YEAR"], $_REQUEST["TERM"], $_REQUEST["ids"])) !== null){
            $this->ajaxReturn($message,"JSON");
            exit;
        }**/

        //todo: 3、启动事务机制插入选课记录
        foreach($_REQUEST["ids"] as $key=>$courseNo){
            $_message = $this->insertCourseToDB($_REQUEST["YEAR"], $_REQUEST["TERM"], session("S_USER_NAME"), $courseNo, $_REQUEST["examType"][$key], 1);
            if($message["type"]!="error") $message["type"] = $_message["type"];
            $message["message"] .= nl2br($_message["message"]);
            if($_message["dbError"] && !$message["dbError"]) $message["dbError"]=$_message["dbError"];
        }
        $this->ajaxReturn($message,"JSON");
        exit;
    }

    /**
     * [ACT]退课列表
     */
    public function removeList(){
        if($this->_hasJson){
            $sql = $this->model->getSqlMap('course/studentR32.sql');
            $bind = $this->model->getBind("YEAR,TERM,STUDENTNO",array(intval($_REQUEST["YEAR"]), intval($_REQUEST["TERM"]), session("S_USER_NAME")));
            $json["total"] = $this->model->sqlCount($sql, $bind, true);

            if($json["total"]>0){
                $sql = $this->model->getPageSql($sql, null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql,$bind);
            }else $json["rows"] = array();
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->display();
    }

    /**
     * [ACT]退课
     */
    public function remove(){
        //todo: 传递回来的课程号为ids数组
        //定义全局消息变量
        $message = array("type"=>"info","message"=>"","dbError"=>"");

        //todo: 0、传入值检测
        //检测学年，学期是否正确
        if(!isset($_REQUEST["YEAR"]) || $_REQUEST["YEAR"]<2000 || !isset($_REQUEST["TERM"]) || $_REQUEST["TERM"]==0 || $_REQUEST["TERM"]>4){
            $message["type"] = "error";
            $message["message"] = "指定的学年和学期有误，请检查！";
            $this->ajaxReturn($message,"JSON");
            exit;
        }
        elseif(!isset($_REQUEST["ids"])){
            $message["type"] = "error";
            $message["message"] = "没有选择任一课程！";
            $this->ajaxReturn($message,"JSON");
            exit;
        }

        //todo: 3、启动事务机制插入选课记录
        foreach($_REQUEST["ids"] as $courseNo){
            $_message = $this->removeCourseFormDB($_REQUEST["YEAR"], $_REQUEST["TERM"], session("S_USER_NAME"), $courseNo);
            if($message["type"]!="error") $message["type"] = $_message["type"];
            $message["message"] .= nl2br($_message["message"]);
            if($_message["dbError"] && !$message["dbError"]) $message["dbError"]=$_message["dbError"];
        }
        $this->ajaxReturn($message,"JSON");
        exit;
    }

    /**
     * [ACT]没选上的课
     */
    public function dump(){
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $bind = $this->model->getBind("YEAR,TERM,STUDENTNO",array(intval($_REQUEST["YEAR"]), intval($_REQUEST["TERM"]), session("S_USER_NAME")));
            $sql = $this->model->getSqlMap("course/studentDump.sql");
            $count = $this->model->sqlCount($sql, $bind ,true);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->display();
    }

    /**
     * {ACT]有空的公选课
     */
    public function free(){
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $bind = $this->model->getBind("R32YEAR,R32TERM,YEAR,TERM",array(intval($_REQUEST["YEAR"]), intval($_REQUEST["TERM"]),intval($_REQUEST["YEAR"]), intval($_REQUEST["TERM"])));
            $sql = $this->model->getSqlMap("course/freeCourse.sql");
            $count = $this->model->sqlCount($sql, $bind ,true);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->display();
    }

    /**
     * 检测所选课程是否有冲突
     * @param $coursenos|array 课程号数组
     * @return null|string
     */
    private function checkStudentSchedule($year, $term, $coursenos){
        //只有一条数据就直接返回null
        if(count($coursenos)==1) return null;
        $sql = $this->model->getSqlMap("course/checkStudentSelectedSchedule.sql");
        $bindIds = "'".@implode("','",$coursenos)."'";
        $sql = str_replace(":COURSENOGROUPS", $bindIds, $sql);
        $data = $this->model->sqlQuery($sql,$this->model->getBind("YEAR,TERM",array($year,$term)));

        $_message = null;
        if(count($data)>0){
            $message = "";
            foreach($data as $row){
                $_message .= "课程".$row["COURSENOGROUP"]."上课时间有冲突\n\n";
            }
        }
        if($_message!==null){
            $message["type"] = "error";
            $message["message"] = nl2br($_message);
            return $message;
        }
        return null;
    }

    /**
     * 以事务机制加入一个课程到选课表中
     * @param $year 学年
     * @param $term 学期
     * @param $studentNo 学号
     * @param $courseNo 课程号
     * @param $fee 是否已收费
     * @return array 返回消息
     */
    private function insertCourseToDB($year, $term, $studentNo, $courseNo, $examtype, $fee){
        //todo: 初始值检查和定义
        $courseNo7 = substr($courseNo,0,7); //7位课号
        $group = substr($courseNo,7); //2位组号
        //$repeat = 0; //是否重修
        $repeat=$this->isRepeat($studentNo,$courseNo);
        if($repeat==1) $fee = 0;//如果是重修把收费改为0
        $conflicts = 0; //是否有冲突
        $inprogram = 0; //是否计划内
        //$inprogram=$this->isInprogram($studentNo, $courseNo);
        $message = array();
        if(!$courseNo || !$examtype){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选定的课程号[".$courseNo."]缺少必要的参数！</font>\n";
            $message["dbError"] = "";
            return $message;
        }

        //todo: 启动事务机制插入选课记录
        $this->model->startTrans();

        //todo: 1、检测课程是否允许选课有2个值，选定哪个呢？ LOCK,HALFLOCK,ESTIMATE,ATTENDENTS
        $lock=$this->isLock($year, $term, $courseNo7, $group);
        if($lock["HALFLOCK"]==1){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选定的课程号[".$courseNo."]没有开放选课！</font>\n";
            $message["dbError"] = "";
            $this->model->rollback();
            return $message;
        }elseif($lock["LOCK"]==1 && $lock["ESTIMATE"]<=$lock["ATTENDENTS"]){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选定的课程号[".$courseNo."]选课人数已达到上限！</font>\n";
            $message["dbError"] = "";
            $this->model->rollback();
            return $message;
        }

        //todo: 2、获得当前学生当前学年学期的课程总表
        $sTimeList = $this->getTimeList($year, $term, $studentNo,"S","");

        //todo: 3、获得指定课程的预计人数和选课人数
        //可以从$lock得到分别为：ESTIMATE,ATTENDENTS，检测人数超额???????????

        //todo: 4、取得课程的TIMELIST
        $pTimeList = $this->getTimeList($year, $term, $courseNo7,"P", $group);
        //todo: 4、1是否有冲突
        foreach($pTimeList as $key=>$val){
            if(in_array($key,$this->_array) && ($val & $sTimeList[$key])>0) {
                $conflicts=1;
                break;
            }
        }

        //todo: 5、得到课程类型和考试类型：COURSENO,COURSETYPE,EXAMTYPE
        $data = $courseAndExamType = $this->getCourseAndExamType($studentNo,$courseNo);
        //如果找到值，说明是计划内课程
        if($data && count($data)>0) {
            $inprogram=1;
            $coursetype = $data["COURSETYPE"];
            $examtype = $data["EXAMTYPE"];
        }else{
            //没有找到时，把修课方式改为选修
            $coursetype = "E";
        }

        //todo: 6、插入到R32表
        $bind = $this->model->getBind("YEAR,TERM,COURSENO,GROUP,STUDENTNO,INPROGRAM,CONFLICTS,REPEAT,FEE,COURSETYPE,EXAMTYPE",
            array($year, $term, $courseNo7,$group,$studentNo,$inprogram,$conflicts, $repeat, $fee, $coursetype, $examtype));
        $data = $this->model->sqlExecute($this->model->getSqlMap("course/insertR32.sql"),$bind);
        if($data===false){
            $message["type"] = "error";
            if( strpos(iconv('GB2312', 'UTF-8',$this->model->getDbError()), "PRIMARY KEY 约束")){
                $message["message"] = "<font color='red'>课程号[".$courseNo."]已有相同课程存在！</font>\n";
            }else{

                $message["message"] = "<font color='red'>把课号[".$courseNo."]保存到选课表中发生错误！</font>\n";
            }
            $message["dbError"] = $this->model->getDbError();
            $this->model->rollback();
            return $message;
        }

        //todo: 7、更新选课人数表
        $bind = $this->model->getBind("YEAR,TERM,COURSENO,GROUP",array($year, $term, $courseNo7, $group));
        $data = $this->model->sqlExecute("UPDATE SCHEDULEPLAN SET ATTENDENTS=ATTENDENTS+1 WHERE YEAR=:YEAR AND TERM=:TERM AND COURSENO=:COURSENO AND [GROUP]=:GROUP",$bind);
        if($data===false){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选择课号[".$courseNo."]更新选课人数时发生错误！</font>\n";
            $message["dbError"] = $this->model->getDbError();
            $this->model->rollback();
            return $message;
        }

        //todo: 8、删除总课时表
        $bind = $this->model->getBind("YEAR,TERM,WHO",array($year, $term, $studentNo));
        $data = $this->model->sqlExecute("DELETE TIMELIST WHERE YEAR=:YEAR AND TERM=:TERM AND WHO=:WHO AND TYPE='S'",$bind);
        if($data===false){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选择课号[".$courseNo."]删除课程总时表时发生错误！</font>\n";
            $message["dbError"] = $this->model->getDbError();
            $this->model->rollback();
            return $message;
        }

        //todo: 9、插入到总时表
        $bind = $this->model->getBind("YEAR,TERM,STUDENTNO",array($year, $term, $studentNo));
        $data = $this->model->sqlQuery($this->model->getSqlMap("course/studentTimeList.sql"),$bind);
        if($data===false){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选择课号[".$courseNo."]插入到课程总时表时发生错误！</font>\n";
            $message["dbError"] = $this->model->getDbError();
            $this->model->rollback();
            return $message;
        }else{
            if($data>0){
                $bind = $this->model->getBind("YEAR,TERM,WHO,TYPE,PARA,MON,TUE,WES,THU,FRI,SAT,SUN",
                    array(
                        $year,$term,$studentNo,'S','',intval($data[0]["MON"]),intval($data[0]["TUE"]),intval($data[0]["WES"]),
                        intval($data[0]["THU"]),intval($data[0]["FRI"]),intval($data[0]["SAT"]),intval($data[0]["SUN"])
                    )
                );
            }else{
                $bind = $this->model->getBind("YEAR,TERM,WHO,TYPE,PARA,MON,TUE,WES,THU,FRI,SAT,SUN",
                    array($year,$term,$studentNo,'S','',0,0,0,0,0,0,0)
                );
            }
            $data = $this->model->sqlExecute($this->model->getSqlMap("course/insertTimeList.sql"),$bind);
            if($data===false){
                $message["type"] = "error";
                $message["message"] = "<font color='red'>选择课号[".$courseNo."]插入到课程总时表时发生错误！</font>\n";
                $message["dbError"] = $this->model->getDbError();
                $this->model->rollback();
                return $message;
            }
        }

        //todo: 6、提交事务
        $this->model->commit();
        $message["type"] = "info";
        $message["message"] = "课程[".$courseNo."]已成功入选！";
        if($repeat==1) $message["message"] .= "<font color='red'>是重修课程，请缴纳重修费！</font>";
        if($conflicts==1) $message["message"] .= "<font color='red'>上课时间有冲突，请进入退课模块做适当修改！</font>";
        $message["message"] .="\n";
        return $message;
    }

    /**
     * 退课逻辑
     * @param $year
     * @param $term
     * @param $studentNo
     * @param $courseNo
     * @return mixed
     */
    private function removeCourseFormDB($year, $term, $studentNo, $courseNo){
        //todo: 初始值定义
        $courseNo7 = substr($courseNo,0,7); //7位课号
        $group = substr($courseNo,7); //2位组号

        //todo: 启动事务机制插入选课记录
        $this->model->startTrans();

        //todo: 1、检查是否可退选
        $lock=$this->isLock($year, $term, $courseNo7, $group);
        if($lock["HALFLOCK"]==1){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选定的课程号[".$courseNo."]不能退课！</font>\n";
            $message["dbError"] = "";
            $this->model->rollback();
            return $message;
        }

        //todo: 2、从选课记录中删除
        $bind = $this->model->getBind("YEAR,TERM,COURSENO,GROUP,STUDENTNO",array($year, $term, $courseNo7, $group, $studentNo));
        $data = $this->model->sqlExecute("DELETE R32 WHERE YEAR=:YEAR AND TERM=:TERM AND COURSENO=:COURSENO AND [GROUP]=:GROUP AND STUDENTNO=:STUDENTNO",$bind);
        if($data===false){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>课号[".$courseNo."]从R32表中删除时发生错误！</font>\n";
            $message["dbError"] = $this->model->getDbError();
            $this->model->rollback();
            return $message;
        }

        //todo: 3、更新已选人数
        $bind = $this->model->getBind("YEAR,TERM,COURSENO,GROUP",array($year, $term, $courseNo7, $group));
        $data = $this->model->sqlExecute("UPDATE SCHEDULEPLAN SET ATTENDENTS=ATTENDENTS-1 WHERE YEAR=:YEAR AND TERM=:TERM AND COURSENO=:COURSENO AND [GROUP]=:GROUP",$bind);
        if($data===false){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选择课号[".$courseNo."]更新选课人数时发生错误！</font>\n";
            $message["dbError"] = $this->model->getDbError();
            $this->model->rollback();
            return $message;
        }

        //todo: 4、删除时间总表
        $bind = $this->model->getBind("YEAR,TERM,WHO",array($year, $term, $studentNo));
        $data = $this->model->sqlExecute("DELETE TIMELIST WHERE YEAR=:YEAR AND TERM=:TERM AND WHO=:WHO AND TYPE='S'",$bind);
        if($data===false){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选择课号[".$courseNo."]删除课程总时表时发生错误！</font>\n";
            $message["dbError"] = $this->model->getDbError();
            $this->model->rollback();
            return $message;
        }

        //todo: 5、插入时间总表
        $bind = $this->model->getBind("YEAR,TERM,STUDENTNO",array($year, $term, $studentNo));
        $data = $this->model->sqlQuery($this->model->getSqlMap("course/studentTimeList.sql"),$bind);
        if($data===false){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选择课号[".$courseNo."]插入到课程总时表时发生错误！</font>\n";
            $message["dbError"] = $this->model->getDbError();
            $this->model->rollback();
            return $message;
        }elseif(count($data)==1){
            $bind = $this->model->getBind("YEAR,TERM,WHO,TYPE,PARA,MON,TUE,WES,THU,FRI,SAT,SUN",
                array(
                    $year,$term,$studentNo,'S','',intval($data[0]["MON"]),intval($data[0]["TUE"]),intval($data[0]["WES"]),
                    intval($data[0]["THU"]),intval($data[0]["FRI"]),intval($data[0]["SAT"]),intval($data[0]["SUN"])
                )
            );
            $data = $this->model->sqlExecute($this->model->getSqlMap("course/insertTimeList.sql"),$bind);
            if($data===false){
                $message["type"] = "error";
                $message["message"] = "<font color='red'>选择课号[".$courseNo."]插入到课程总时表时发生错误！</font>\n";
                $message["dbError"] = $this->model->getDbError();
                $this->model->rollback();
                return $message;
            }
        }

        //todo: 6、提交事务
        $this->model->commit();
        $message["type"] = "info";
        $message["message"] = "课程[".$courseNo."]已成功从课表中删除！\n";
        return $message;
    }

    /**
     * 获得一个学生的一周上课时间表
     * @param $year
     * @param $term
     * @param $studentNo
     * @return null|array
     */
    private function getTimeList($year, $term, $studentNo, $type="S", $group=''){
        $bind = $this->model->getBind("TYPE,PARA,YEAR,TERM,WHO",array($type, $group, $year, $term, $studentNo));
        $data = $this->model->sqlFind($this->model->getSqlMap("course/getTimeList.sql"),$bind);
        if(!$data || count($data)==0) return null;
        return $data;
    }


    /**
     * 学生课程周总表和选择的课程表是否有冲突
     * @param $totalTimeList 课程总表
     * @param $selectTimeList 单一课程表
     * @param string $key 星期的key名称
     * @param string $value 星期的value名称
     * @return bool
     */
    private function isConflicts($totalTimeList, $selectTimeList, $key="DAY", $value="TIMEBITS"){
        if(!$totalTimeList || $selectTimeList) return 0;
        foreach($selectTimeList as $row){
            $weekField = $this->_array[$row[$key]];
            if(($row[$value] & $selectTimeList[$weekField])>0) return 1;
        }
        return 0;
    }

    /**
     * 是否为重修课程
     * @param $studentNo
     * @param $courseNo
     * @return int
     */
    private function isRepeat($studentNo, $courseNo){
        if(!$courseNo || strlen($courseNo)<6) return 0;
        $bind = $this->model->getBind("YEAR,TERM,STUDENTNO,COURSENO", array($studentNo, substr($courseNo,0,6)));
        $data = $this->model->sqlFind($this->model->getSqlMap("course/getRepeatStatue.sql"),$bind);
        if($data["ROWS"]>0) return 1;
        else return 0;
    }

    /**
     * 是否计划内课程
     * @param $studentNo
     * @param $courseNo
     * @return int
     */
    private function isInprogram($studentNo, $courseNo){
        if(!$courseNo || strlen($courseNo)<7) return 0;
        $data = $this->model->sqlFind($this->model->getSqlMap("course/getInprogramStatue.sql"),array(":STUDENTNO"=>$studentNo,":COURSENO"=>substr($courseNo,0,7)));
        if($data["ROWS"]>0) return 1;
        return 0;
    }

    /**
     * 是否已收费
     * @param $studentNo
     * @return int
     */
    private function isFee($studentNo){
        //$data = $this->model->sqlFind($this->model->getSqlMap("course/getFeeStatue.sql"),array(":STUDENTNO"=>$studentNo));
        //if(!isset($data[0]) || !$data[0]["Study"]) return 0;
        $user = session("S_USER_INFO");
        if(isset($user["FREE"]) && $user["FREE"]==0) return 1;
        return 0;
    }

    /**
     * 课程是否允许选择
     *
     * @param $year
     * @param $term
     * @param $courseNo
     * @param null|array[LOCK,HALFLOCK,ESTIMATE,ATTENDENTS]
     */
    private function isLock($year, $term, $courseNo, $group=null){
        if($group==null) $group = substr($courseNo, -2);
        $bind = $this->model->getBind("YEAR,TERM,COURSENO,GROUP",array($year, $term, substr($courseNo,0,7), $group));
        return $this->model->sqlFind($this->model->getSqlMap("course/getLockStatue.sql"),$bind);
    }

    /**
     * 取得课程的类型和考试类型
     * @param $studentNo
     * @param $courseNo
     * @return mixed|array(COURSENO,COURSETYPE,EXAMTYPE)
     */
    private function getCourseAndExamType($studentNo, $courseNo){
        $bind = $this->model->getBind("STUDENTNO,COURSENO",array($studentNo, substr($courseNo,0,6)));
        //$bind = $this->model->getBind("COURSENO",array($courseNo));
        return $this->model->sqlFind($this->model->getSqlMap("course/getCourseAndExamType.sql"),$bind);
    }

    /**
     * 是否还有考评没有做
     * @param $studentNo
     * @return bool
     */
    private function isKaopingLock($studentNo){
        $count = $this->model->sqlCount($this->model->getSqlMap("kaoping/kaoping_lock.sql"),array(":studentno"=>$studentNo));
        if($count>0) return true;
        else return false;
    }
}