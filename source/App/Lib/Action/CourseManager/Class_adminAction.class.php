<?php

//todo:班级选课管理
class Class_adminAction extends RightAction
{
    private $md;        //存放模型对象
    private $base;      //路径
    /**
     *   学期课表
     *
     **/
    public function __construct(){
        parent::__construct();
        $this->md=new Sqlsrvmodel();
        $this->base='CourseManager/';
    }

    public function  Four_classcourse(){
        if($this->_hasJson){
            $data_List=array();
            //todo:查询出积点和 (每个学生一条的数据)
            $credit=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Four_four_studentANDcredit.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':CLASSNO'=>$_POST['CLASSNO']));
            //todo:查询出 这个班上的课程
            //          $course=$this->md->sqlQuery($this->md->getSqlMap($this->base.'One_Four_selectCourse.SQL'),array(':CLASSNO'=>$_POST['CLASSNO'],':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));
            //todo:查询出 学生所对应的课程列表  和成绩
            $student=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Four_four_studentANDcourse.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':CLASSNO'=>$_POST['CLASSNO']));

            $inum=count($credit);
            $jnum=count($student);

            //todo:循环学分列表
            for($i=0;$i<$inum;$i++){                            //todo:学分的
                $data_List[$i]=$credit[$i];
                for($j=0;$j<$jnum;$j++){                                                            //todo:课程列表和成绩(最多的那些)
                    if($credit[$i]['xh']!=$student[$j]['STUDENTNO']){
                        continue;
                    }
                    $data_List[$i]['a'.strtoupper($student[$j]['COURSENOGROUP'])]=$student[$j]['COURSETYPE'];
                }
            }
            $arr['total']=count($data_List);
            $arr['rows']=$data_List;

            echo json_encode($arr);
            exit;

        }

        $this->display();

    }


    public function getTime($arr){
        $ar2=array();
        foreach($arr as $val){
            $ar2[$val['NAME']]=$val;
        }
        return $ar2;
    }

    //todo:选课统计
    public function Five_count(){
        $this->xiala('schools','schools');
        $this->display();
    }


    //todo:班级周课表方法
    public function Class_week_course(){


        $array=array();                 //todo:存放前台的list数组
        $OEW = array("B"=>"","E"=>"（双周）","O"=>"（单周）");      //todo:单双周数组
        // todo:查询出该教室该学年的课程安排
        $arr=$this->md->sqlQuery($this->md->getSqlMap('CourseManager/Four_five_ClassCourse.SQL'),array(':YEAR'=>$_GET['year'],':TERM'=>$_GET['term'],':CLASSNO'=>$_GET['CLASSNO']));

        //todo:查询标题名称
        $title_CLASSNAME=$this->md->sqlFind("SELECT RTRIM(CLASSNAME) AS CLASSNAME FROM CLASSES WHERE RTRIM(CLASSNO)='{$_GET['CLASSNO']}'");



        $jieci=$this->md->sqlQuery("select * from TIMESECTORS");//todo:节次数组
        $jieci=$this->getTime($jieci);
        $countOnejieci=array_reduce($jieci, "countOneDay");              //todo:统计出一天几个单节课
        foreach($arr as $key=>$val){
            if($val['WEEKS']!=262143){
                $weeks='周次'.str_pad(strrev(decbin($val['WEEKS'])),18,0);
            }else{
                $weeks='';
            }

            for($i=1;$i<count($countOnejieci);$i+=2){
                for($j=0;$j<2;$j++){
                    if(($jieci[$val['TIME'][0]]['TIMEBITS'] & $jieci[$countOnejieci[$i-1+$j]]['TIMEBITS'])>0){
                        if($jieci[$val['TIME'][0]]['UNIT']=="3"){
                            //todo:取最后一节课是第几节
                            $len=strlen(strrev(decbin($jieci[$val['TIME'][0]]['TIMEBITS'])));
                            //todo:表示到单节了
                            if(!($i+1<$len)){
                                $array[($i-1)/2+1][$val['TIME'][1]] .='(第'.$len.'节)'.$OEW[$val['TIME'][2]].$val["COURSE"]."{$weeks}" ;
                            }else{
                                $array[($i-1)/2+1][$val['TIME'][1]] .=$OEW[$val['TIME'][2]].$val["COURSE"]."{$weeks}<br/>";
                            }
                            break;
                        }
                        //todo:是一节课的就加上(第几节)  否则为空
                        $array[($i-1)/2+1][$val['TIME'][1]] .= ($jieci[$val['TIME'][0]]['UNIT']=="1" ? '('.trim($jieci[$val['TIME'][0]]['VALUE']).')' : '').
                            $OEW[$val['TIME'][2]].$val["COURSE"]."{$weeks}<br/>";
                        break;
                    }
                }
            }
        }


        $web = A('Room/Room');
        $str=$web->web($array,$title_CLASSNAME['CLASSNAME'],date('Y-m-d H:i:s'),$_GET);

        $this->assign('content',$str);
        $this->display();
    }

    /*
     * 我的个人页面首页
     */
    public function My_student_Page(){

        $this->assign('year',$_REQUEST);

        if(!isset($_GET['YEAR'])){
            $this->assign('year_term',$this->md->sqlFind("select * from YEAR_TERM WHERE TYPE='S'"));
        }ELSE{
            $this->assign('year_term',array('YEAR'=>$_GET['YEAR'],'TERM'=>$_GET['TERM']));
        }
        //todo:给页面赋值学生信息
        $userinfo=$this->md->sqlFind("SELECT RTRIM(STUDENTS.NAME) AS name, STUDENTS.FREE, STUDENTS.CLASSNO,CLASSES.CLASSNAME FROM STUDENTS INNER JOIN CLASSES ON STUDENTS.CLASSNO = CLASSES.CLASSNO where studentno='{$_GET['username']}'");
        //todo:把学生信息保存到session
        session('student_info',$userinfo);
        session('x_username',$userinfo['name']);
        session('x_classno',$userinfo['CLASSNO']);
        session('x_classname',$userinfo['CLASSNAME']);
        session('studnet_studentno',$_GET['username']);


/*
        var_dump($_GET['username']);
        var_dump($_SESSION);

*/

        $this->assign('studentno',$_GET['username']);
        // $this->assign('info',);
        $this->display();
    }


    /*
     * 班级课表
     */
    public function myclasstime(){
        $data = $this->md->sqlFind($this->md->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
        $year = $_GET['YEAR'];
        $term = $_GET['TERM'];
        $userInfo = session("S_USER_INFO");
        $bind = $this->md->getBind("YEAR,TERM,CLASSNO",array($year, $term,session('x_classno')));
        $data = $this->md->sqlQuery($this->md->getSqlMap("timeTable/getClass.sql"),$bind);


        //  $gT=A('Student/Timetable');           //todo :私有方法 对象不能调用
        //var_dump(method_exists($gT,'getTimeTable'));

        $this->assign("list",$this->getTimeTable($data));
        $arr=$this->getTimeTable($data);
        if(isset($_GET['daying'])){
            $this->assign('daying','true');
        }else{
            $this->assign('daying','false');
        }
        $this->assign("YEAR",$year);
        $this->assign("TERM", $term);
        $this->display();
    }



    /*
     * 周课表
     */
    public function myWeekTime(){

        $data = $this->md->sqlFind($this->md->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
        $year = $_GET['YEAR'];
        $term = $_GET['TERM'];

        $bind = $this->md->getBind("YEAR,TERM,STUDENTNO",array($year, $term, session("studnet_studentno")));
        $data = $this->md->sqlQuery($this->md->getSqlMap("timeTable/getWeek.sql"),$bind);
        $this->assign("list",$this->getTimeTable($data));
        if(isset($_GET['daying'])){
            $this->assign('daying','true');
        }else{
            $this->assign('daying','false');
        }
        $this->assign("YEAR",$year);
        $this->assign("TERM", $term);
        $this->display();
    }


    /*
     * 选课-------------页面
     */
    public function xuanke(){

        $this->assign("isKaopingLock",$this->isKaopingLock(session("studnet_studentno")));

        $this->display();
    }



    /**
     * 选课列表-------------页面
     */
    public function qlist(){

        if($this->_hasJson){
            $bind = $this->md->getBind("YEAR,TERM,COURSENOGROUP,COURSENAME,TEACHERNAME,COURSETYPE,SCHOOL,CLASSNO,DAY,TIME", $_REQUEST, "%");

            $data = $this->md->sqlFind($this->md->getSqlMap("course/countStudentQuerySchedule.sql"), $bind);
            $json["total"] = $data['ROWS'];

            if($json["total"]>0){
                $sql = $this->md->getPageSql(null,"course/studentQuerySchedule.sql", $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->md->sqlQuery($sql, $bind);
            }else $json["rows"] = array();
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->assign("queryParams",count($_REQUEST)>0?json_encode($_REQUEST):"{}");
        $this->display();
    }


    /*
     * 选课确认-------------------逻辑
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

        /*        //todo: 1、检测是否已缴费
                if($this->isFee(session("studnet_studentno"))==0){
                    $message["type"] = "error";
                    $message["message"] = "您还没有交纳学费，不能进行选课！";
                    $this->ajaxReturn($message,"JSON");
                    exit;
                }*/

        //todo: 2、对比学生ids数组中的选择课程是否有冲突
        /**
        if(($message = $this->checkStudentSchedule($_REQUEST["YEAR"], $_REQUEST["TERM"], $_REQUEST["ids"])) !== null){
        $this->ajaxReturn($message,"JSON");
        exit;
        }**/

        //todo: 3、启动事务机制插入选课记录
        foreach($_REQUEST["ids"] as $key=>$courseNo){
            $_message = $this->insertCourseToDB($_REQUEST["YEAR"], $_REQUEST["TERM"], session("studnet_studentno"), $courseNo, $_REQUEST["examType"][$key], 1);
            if($message["type"]!="error") $message["type"] = $_message["type"];
            $message["message"] .= nl2br($_message["message"]);
            if($_message["dbError"] && !$message["dbError"]) $message["dbError"]=$_message["dbError"];
        }
        $this->ajaxReturn($message,"JSON");
        exit;
    }





    /**
     * 是否已收费
     * @param $studentNo
     * @return int
     */
    private function isFee($studentNo){
        //$data = $this->md->sqlFind($this->md->getSqlMap("course/getFeeStatue.sql"),array(":STUDENTNO"=>$studentNo));
        //if(!isset($data[0]) || !$data[0]["Study"]) return 0;
        $user = session("student_info");

        if(isset($user["FREE"]) && $user["FREE"]==0) return 1;
        return 0;
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
        $this->md->startTrans();

        //todo: 1、检测课程是否允许选课有2个值，选定哪个呢？ LOCK,HALFLOCK,ESTIMATE,ATTENDENTS
        $lock=$this->isLock($year, $term, $courseNo7, $group);


        /*if($lock["HALFLOCK"]==1){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选定的课程号[".$courseNo."]没有开放选课！</font>\n";
            $message["dbError"] = "";
            $this->md->rollback();
            return $message;
        }else*/
        if($lock["LOCK"]==1 && $lock["ESTIMATE"]<=$lock["ATTENDENTS"]){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选定的课程号[".$courseNo."]选课人数已达到上限！</font>\n";
            $message["dbError"] = "";
            $this->md->rollback();
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
        $bind = $this->md->getBind("YEAR,TERM,COURSENO,GROUP,STUDENTNO,INPROGRAM,CONFLICTS,REPEAT,FEE,COURSETYPE,EXAMTYPE",
            array($year, $term, $courseNo7,$group,$studentNo,$inprogram,$conflicts, $repeat, $fee, $coursetype, $examtype));
        $data = $this->md->sqlExecute($this->md->getSqlMap("course/insertR32.sql"),$bind);
        if($data===false){
            $message["type"] = "error";
            if(strpos($this->md->getDbError(), "PRIMARY KEY")){
                $message["message"] = "<font color='red'>课程号[".$courseNo."]已有相同课程存在！</font>\n";
            }else{
                $message["message"] = "<font color='red'>把课号[".$courseNo."]保存到选课表中发生错误！</font>\n";
            }
            $message["dbError"] = $this->md->getDbError();
            $this->md->rollback();
            return $message;
        }

        //todo: 7、更新选课人数表
        $bind = $this->md->getBind("YEAR,TERM,COURSENO,GROUP",array($year, $term, $courseNo7, $group));
        $data = $this->md->sqlExecute("UPDATE SCHEDULEPLAN SET ATTENDENTS=ATTENDENTS+1 WHERE YEAR=:YEAR AND TERM=:TERM AND COURSENO=:COURSENO AND [GROUP]=:GROUP",$bind);
        if($data===false){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选择课号[".$courseNo."]更新选课人数时发生错误！</font>\n";
            $message["dbError"] = $this->md->getDbError();
            $this->md->rollback();
            return $message;
        }

        //todo: 8、删除总课时表
        $bind = $this->md->getBind("YEAR,TERM,WHO",array($year, $term, $studentNo));
        $data = $this->md->sqlExecute("DELETE TIMELIST WHERE YEAR=:YEAR AND TERM=:TERM AND WHO=:WHO AND TYPE='S'",$bind);
        if($data===false){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选择课号[".$courseNo."]删除课程总时表时发生错误！</font>\n";
            $message["dbError"] = $this->md->getDbError();
            $this->md->rollback();
            return $message;
        }

        //todo: 9、插入到总时表
        $bind = $this->md->getBind("YEAR,TERM,STUDENTNO",array($year, $term, $studentNo));
        $data = $this->md->sqlQuery($this->md->getSqlMap("course/studentTimeList.sql"),$bind);
        if($data===false){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选择课号[".$courseNo."]插入到课程总时表时发生错误！</font>\n";
            $message["dbError"] = $this->md->getDbError();
            $this->md->rollback();
            return $message;
        }else{
            if($data>0){
                $bind = $this->md->getBind("YEAR,TERM,WHO,TYPE,PARA,MON,TUE,WES,THU,FRI,SAT,SUN",
                    array(
                        $year,$term,$studentNo,'S','',intval($data[0]["MON"]),intval($data[0]["TUE"]),intval($data[0]["WES"]),
                        intval($data[0]["THU"]),intval($data[0]["FRI"]),intval($data[0]["SAT"]),intval($data[0]["SUN"])
                    )
                );
            }else{
                $bind = $this->md->getBind("YEAR,TERM,WHO,TYPE,PARA,MON,TUE,WES,THU,FRI,SAT,SUN",
                    array($year,$term,$studentNo,'S','',0,0,0,0,0,0,0)
                );
            }
            $data = $this->md->sqlExecute($this->md->getSqlMap("course/insertTimeList.sql"),$bind);
            if($data===false){
                $message["type"] = "error";
                $message["message"] = "<font color='red'>选择课号[".$courseNo."]插入到课程总时表时发生错误！</font>\n";
                $message["dbError"] = $this->md->getDbError();
                $this->md->rollback();
                return $message;
            }
        }

        //todo: 6、提交事务
        $this->md->commit();
        $message["type"] = "info";
        $message["message"] = "课程[".$courseNo."]已成功入选！";
        if($repeat==1) $message["message"] .= "<font color='red'>是重修课程，请缴纳重修费！</font>";
        if($conflicts==1) $message["message"] .= "<font color='red'>上课时间有冲突，请进入退课模块做适当修改！</font>";
        $message["message"] .="\n";
        return $message;
    }



    private function getTimeList($year, $term, $studentNo, $type="S", $group=''){
        $bind = $this->md->getBind("TYPE,PARA,YEAR,TERM,WHO",array($type, $group, $year, $term, $studentNo));
        $data = $this->md->sqlFind($this->md->getSqlMap("course/getTimeList.sql"),$bind);
        if(!$data || count($data)==0) return null;
        return $data;
    }


    /**
     * 是否为重修课程
     * @param $studentNo
     * @param $courseNo
     * @return int
     */
    private function isRepeat($studentNo, $courseNo){
        if(!$courseNo || strlen($courseNo)<6) return 0;
        $bind = $this->md->getBind("YEAR,TERM,STUDENTNO,COURSENO", array($studentNo, doWithBindStr(substr($courseNo,0,6))));
        $data = $this->md->sqlFind($this->md->getSqlMap("course/getRepeatStatue.sql"),$bind);
        if($data["ROWS"]>0) return 1;
        else return 0;
    }


    /**
     * [ACT]退课列表
     */
    public function removeList(){

        if($this->_hasJson){
            $sql = $this->md->getSqlMap('course/studentR32.sql');
            $bind = $this->md->getBind("YEAR,TERM,STUDENTNO",array(intval($_REQUEST["YEAR"]), intval($_REQUEST["TERM"]), session("studnet_studentno")));
            $json["total"] = $this->md->sqlCount($sql, $bind, true);

            if($json["total"]>0){
                $sql = $this->md->getPageSql($sql, null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->md->sqlQuery($sql,$bind);
            }else $json["rows"] = array();
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->display();
    }






    /**
     * [ACT]退课操作
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
            $_message = $this->removeCourseFormDB($_REQUEST["YEAR"], $_REQUEST["TERM"], session("studnet_studentno"), $courseNo);
            if($message["type"]!="error") $message["type"] = $_message["type"];
            $message["message"] .= nl2br($_message["message"]);
            if($_message["dbError"] && !$message["dbError"]) $message["dbError"]=$_message["dbError"];
        }
        $this->ajaxReturn($message,"JSON");
        exit;
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
        $this->md->startTrans();

        /*   //todo: 1、检查是否可退选
           $lock=$this->isLock($year, $term, $courseNo7, $group);
           echo '<pre>';
           var_dump($lock);
           exit;
           if($lock["HALFLOCK"]==1){
               $message["type"] = "error";
               $message["message"] = "<font color='red'>选定的课程号[".$courseNo."]不能退课！</font>\n";
               $message["dbError"] = "";
               $this->md->rollback();
               return $message;
           }*/

        //todo: 2、从选课记录中删除
        $bind = $this->md->getBind("YEAR,TERM,COURSENO,GROUP,STUDENTNO",array($year, $term, $courseNo7, $group, $studentNo));
        $data = $this->md->sqlExecute("DELETE R32 WHERE YEAR=:YEAR AND TERM=:TERM AND COURSENO=:COURSENO AND [GROUP]=:GROUP AND STUDENTNO=:STUDENTNO",$bind);

        if($data===false){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>课号[".$courseNo."]从R32表中删除时发生错误！</font>\n";
            $message["dbError"] = $this->md->getDbError();
            $this->md->rollback();
            return $message;
        }

        //todo: 3、更新已选人数
        $bind = $this->md->getBind("YEAR,TERM,COURSENO,GROUP",array($year, $term, $courseNo7, $group));
        $data = $this->md->sqlExecute("UPDATE SCHEDULEPLAN SET ATTENDENTS=ATTENDENTS-1 WHERE YEAR=:YEAR AND TERM=:TERM AND COURSENO=:COURSENO AND [GROUP]=:GROUP",$bind);
        if($data===false){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选择课号[".$courseNo."]更新选课人数时发生错误！</font>\n";
            $message["dbError"] = $this->md->getDbError();
            $this->model->rollback();
            return $message;
        }

        //todo: 4、删除时间总表
        $bind = $this->md->getBind("YEAR,TERM,WHO",array($year, $term, $studentNo));
        $data = $this->md->sqlExecute("DELETE TIMELIST WHERE YEAR=:YEAR AND TERM=:TERM AND WHO=:WHO AND TYPE='S'",$bind);
        if($data===false){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选择课号[".$courseNo."]删除课程总时表时发生错误！</font>\n";
            $message["dbError"] = $this->md->getDbError();
            $this->md->rollback();
            return $message;
        }

        //todo: 5、插入时间总表
        $bind = $this->md->getBind("YEAR,TERM,STUDENTNO",array($year, $term, $studentNo));
        $data = $this->md->sqlQuery($this->md->getSqlMap("course/studentTimeList.sql"),$bind);
        if($data===false){
            $message["type"] = "error";
            $message["message"] = "<font color='red'>选择课号[".$courseNo."]插入到课程总时表时发生错误！</font>\n";
            $message["dbError"] = $this->md->getDbError();
            $this->md->rollback();
            return $message;
        }elseif(count($data)==1){
            $bind = $this->md->getBind("YEAR,TERM,WHO,TYPE,PARA,MON,TUE,WES,THU,FRI,SAT,SUN",
                array(
                    $year,$term,$studentNo,'S','',intval($data[0]["MON"]),intval($data[0]["TUE"]),intval($data[0]["WES"]),
                    intval($data[0]["THU"]),intval($data[0]["FRI"]),intval($data[0]["SAT"]),intval($data[0]["SUN"])
                )
            );
            $data = $this->md->sqlExecute($this->md->getSqlMap("course/insertTimeList.sql"),$bind);
            if($data===false){
                $message["type"] = "error";
                $message["message"] = "<font color='red'>选择课号[".$courseNo."]插入到课程总时表时发生错误！</font>\n";
                $message["dbError"] = $this->md->getDbError();
                $this->md->rollback();
                return $message;
            }
        }

        //todo: 6、提交事务
        $this->md->commit();
        $message["type"] = "info";
        $message["message"] = "课程[".$courseNo."]已成功从课表中删除！\n";
        return $message;
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
        $bind = $this->md->getBind("YEAR,TERM,COURSENO,GROUP",array($year, $term, substr($courseNo,0,7), $group));
        return $this->md->sqlFind($this->md->getSqlMap("course/getLockStatue.sql"),$bind);
    }





    /**
     * 取得课程的类型和考试类型
     * @param $studentNo
     * @param $courseNo
     * @return mixed|array(COURSENO,COURSETYPE,EXAMTYPE)
     */
    private function getCourseAndExamType($studentNo, $courseNo){
        $bind = $this->md->getBind("STUDENTNO,COURSENO",array($studentNo,doWithBindStr( substr($courseNo,0,6))));
        //$bind = $this->model->getBind("COURSENO",array($courseNo));
        return $this->md->sqlFind($this->md->getSqlMap("course/getCourseAndExamType.sql"),$bind);
    }





    private function isKaopingLock($studentNo){
        $count = $this->md->sqlCount($this->md->getSqlMap("kaoping/kaoping_lock.sql"),array(":studentno"=>$studentNo));
        if($count>0) return true;
        else return false;
    }

    /*
     * 两个处理周课表的方法
     */
    private function getTimeTable($data, $rowspan=2){
        $list = array();
        if(!count($data)) return $list;

        $timeData = $this->md->sqlQuery("select NAME,VALUE,UNIT,TIMEBITS from TIMESECTORS");
        //所有课时列表以NAME为索引
        $timesectors = array_reduce($timeData, "myTimesectorsReduce");
        //取得单节课时自然数为索引
        $countTimesectors = array_reduce($timeData, "myCountTimesectors");
        //单双周
        $both = array("B"=>"","E"=>"（双周）","O"=>"（单周）");

        foreach($data as $row){
            $list = $this->makeTime($list,$row,$rowspan, $both, $timesectors,$countTimesectors);
        }
        //dump($list);

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
//todo========================================================================

    //todo:班级统一必修课 & 非必修课
    public function count_bixiu(){

        ini_set("max_execution_time", "1800");
        $this->display();
        $str='';
        //todo:判断是否有权限
        $one=$this->md->sqlfind("select TEACHERS.SCHOOL from TEACHERS where TEACHERS.TEACHERNO='{$_SESSION['S_USER_INFO']['TEACHERNO']}'");

        //todo:查询出班级的学院
        $classSCHOOL=$this->md->sqlExecute("SELECT SCHOOL FROM CLASSES WHERE CLASSNO='{$_GET['CLASSNO']}'");

        if($one['SCHOOL']!=$classSCHOOL&&$one['SCHOOL']!='A1'){
            exit('您不能替其他学院的操作');
        }
        //todo:查询出该班级的学生
        $studentList=$this->md->sqlQuery("SELECT STUDENTNO FROM STUDENTS WHERE CLASSNO='{$_GET['CLASSNO']}'");

        //todo:查询有必修课吗
        $three=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Four_Tongyi_three.SQL'),array(':CLASSNO'=>$_GET['CLASSNO'],':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM']));

        //todo:没有必修课
        if(!$three){

            foreach($studentList as $val){

                //todo:查询 出学生在TIMELIST 表中的信息
                $student=$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_Tongyi_four.SQL'),array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':WHO'=>$val['STUDENTNO']));
                //todo:删除 学生在TIMELIST表中的信息
                $del=$this->md->sqlExecute($this->md->getSqlMap($this->base.'Four_Tongyi_deleteTimeList.SQL'),array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':WHO'=>$val['STUDENTNO']));
                //todo:往  TIMELIST表中插入信息
                $this->md->sqlExecute($this->md->getSqlMap($this->base.'Four_Tongyi_insertTimeList.SQL'),
                    array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':WHO'=>$val['STUDENTNO'],':TYPE'=>'S',':PARA'=>'',':MON'=>$student['MON'],':TUE'=>$student['TUE'],':WES'=>$student['WES'],
                        ':THU'=>$student['THU'],':FRI'=>$student['FRI'],':SAT'=>$student['SAT'],':SUN'=>$student['SUN']));
            }
            $str.='操作成功，请学生进入自己的选课单查看所选课程情况。';
        }else{      //todo:有必修课


            /*  foreach($three as $key=>$val){
                  $course_no=substr($val['COURSENOGROUP'],0,7);        //todo:7位
                  $course_no2=substr($val['COURSENOGROUP'],0,6);       //todo:6位
                  $gp=substr($val['COURSENOGROUP'],7);
                  //todo:查询出课程的TimeList
                  $courseTimeList=$this->md->sqlFind("SELECT MON,TUE,WES,THU,FRI,SAT,SUN FROM TIMELIST WHERE (YEAR={$_POST['YEAR']}) AND(TERM={$_POST['TERM']}) AND(WHO='{$course_no}') AND(TYPE='P') AND (PARA='$gp')");
              }*/
            $start2=time();
            foreach($studentList as $val){

                //todo:查询 出学生在TIMELIST 表中的信息
                $student=$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_Tongyi_four.SQL'),array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':WHO'=>$val['STUDENTNO']));
                $start=time();
                //todo:循环必修课
                foreach($three as $j){
                    $conflicts=0;
                    $repeat=0;
                    $fee=0;



                    //todo: 判断人数有超吗
                    $num=$this->md->sqlFind("SELECT LOCK,HALFLOCK,ATTENDENTS,ESTIMATE FROM SCHEDULEPLAN WHERE YEAR={$_GET['YEAR']} AND TERM={$_GET['TERM']} AND RTRIM(COURSENO)+RTRIM([GROUP])='{$j['COURSENOGROUP']}'");

                    if($num['ATTENDENTS']>=$num['ESTIMATE']){        //todo:如果人数超了
                        $str.=$j['COURSENOGROUP'].'选课人数超过预计人数或教室座位数限制，请调整教室或预计人数！<br>';
                        if($num['ATTENDENTS']-$num['ESTIMATE']>5){
                            $str.='课程:'.$j['COURSENOGROUP'].'的选课人数已超过预计人数的5人以上，被确认的可能性较低，请通知同学检查他的选课表注意选课确认情况！<br>';
                        }
                    }

                    $course_no=substr($j['COURSENOGROUP'],0,7);        //todo:7位
                    $course_no2=substr($j['COURSENOGROUP'],0,6);       //todo:6位
                    $gp=substr($j['COURSENOGROUP'],7);
                    //todo:查询出课程的TimeList
                    $courseTimeList=$this->md->sqlFind("SELECT MON,TUE,WES,THU,FRI,SAT,SUN FROM TIMELIST WHERE (YEAR={$_GET['YEAR']}) AND(TERM={$_GET['TERM']}) AND(WHO='{$course_no}') AND(TYPE='P') AND (PARA='$gp')");


                    //todo:怎么判断 012W21B00和他已选的课程上课时间有冲突，建议他进入退选程序，适当退选课程。   &吗？
                    foreach($courseTimeList as $key=>$s){

                        if($s&$student[$key]){
                            $str.=$val['STUDENTNO'].'所选的课程'. $j['COURSENOGROUP']."和他已选的课程上课时间有冲突，建议他进入退选程序，适当退选课程。<br>";
                            $conflicts=1;
                            break;
                        }
                    }

                    if(!$conflicts){
                        //todo:更新TimeList
                        $val['MON']|=$courseTimeList['MON'];
                        $val['TUE']|=$courseTimeList['TUE'];
                        $val['WES']|=$courseTimeList['WES'];
                        $val['THU']|=$courseTimeList['THU'];
                        $val['FRI']|=$courseTimeList['FRI'];
                        $val['SAT']|=$courseTimeList['SAT'];
                        $val['SUN']|=$courseTimeList['SUN'];
                    }

                    //todo:查询  COURSETYPE EXAMTYPE
                    $courseType=$this->md->sqlFind("SELECT COURSENO,COURSETYPE,EXAMTYPE FROM VIEWSTUDENTPLANCOURSE WHERE STUDENTNO=:studentno AND COURSENO LIKE '$course_no2%'",array(':studentno'=>$val['STUDENTNO']));

                    //todo:判断成绩单是否有了
                    $chengjidan=$this->md->sqlFind("SELECT COURSENO FROM SCORES WHERE STUDENTNO='{$val['STUDENTNO']}' AND COURSENO LIKE '{$course_no2}%'");

                    if($chengjidan){
                        $str.=$val['STUDENTNO'].'所选的课程'.$j['COURSENOGROUP'].'已经包含在他的成绩单中，他需要交纳重修费才能重修此课程。<br>';
                        $repeat=1;
                    }

                    //todo:往R32插入
                    $r32=$this->md->sqlExecute($this->md->getSqlMap($this->base.'bixiu_insert32.SQL'),
                        array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':COURSENO'=>$course_no,':[GROUP]'=>$gp,':STUDENTNO'=>$val['STUDENTNO'],
                            ':INPROGRAM'=>1,':CONFLICTS'=>$conflicts,':REPEAT'=>$repeat,':FEE'=>$fee,':COURSETYPE'=>$courseType['COURSETYPE'],':EXAMTYPE'=>$courseType['EXAMTYPE']));

                    if(!$r32){
                        $str.=$val['STUDENTNO'].'在向数据库提交课程'. $j['COURSENOGROUP']."时发生异常，这门课程可能已经在他的选课单中了！<br>";
                        continue;
                    }



                    //todo:更新scheduleplan人数
                    $this->md->sqlExecute("UPDATE SCHEDULEPLAN SET ATTENDENTS=ATTENDENTS+1 WHERE YEAR={$_GET['YEAR']} AND TERM={$_GET['TERM']} AND COURSENO='$course_no' AND [GROUP]='$gp'");





                }

                //todo:删除学生TIMELIST
                $this->md->sqlExecute("DELETE TIMELIST WHERE YEAR={$_GET['YEAR']} AND TERM={$_GET['TERM']} AND WHO='{$val['STUDENTNO']}' AND TYPE='S'");



                //todo:重新插入学生TIMELIST;
                $this->md->sqlExecute("INSERT INTO TIMELIST (YEAR,TERM,WHO,TYPE,PARA,MON,TUE,WES,THU,FRI,SAT,SUN)
               VALUES({$_GET['YEAR']},{$_GET['TERM']},'{$val['STUDENTNO']}','S','',{$val['MON']},{$val['TUE']},{$val['WES']},{$val['THU']},{$val['FRI']},{$val['SAT']},{$val['SUN']})");
            }
            $end2=time();
            echo $end2-$start2.'秒';

        }

        echo $str;
        $this->assign('content',$str);


    }



    //todo:修改学生密码
    public function update_password(){

        $one=$this->md->sqlFind("select TEACHERS.SCHOOL from TEACHERS,USERS where USERS.TEACHERNO=TEACHERS.TEACHERNO AND USERS.USERNAME='{$_SESSION['S_USER_NAME']}'");


        $two=$this->md->sqlFind("SELECT SCHOOL FROM STUDENTS WHERE STUDENTNO='{$_POST['STUDENTNO']}'");

        if($one['SCHOOL']!='A1'&&$one['SCHOOL']!=$two['SCHOOL']){
            exit('您不能修改其他学院的学生');
        }
        //todo:修改密码
        $three=$this->md->sqlExecute("UPDATE STUDENTS SET PASSWORD='{$_POST['PASSWORD']}',PASSEXPIRED=0 WHERE STUDENTNO='{$_POST['STUDENTNO']}'");
        if($three){
            exit('密码修改成功');
        }
    }



    //todo:班级统一必修课 & 非必修课
    public function count_feibixiu(){

        ini_set("max_execution_time", "1800");

        $str='';
        //todo:判断是否有权限
        $one=$this->md->sqlfind("select TEACHERS.SCHOOL from TEACHERS where TEACHERS.TEACHERNO='{$_SESSION['S_USER_INFO']['TEACHERNO']}'");

        //todo:查询出班级的学院
        $classSCHOOL=$this->md->sqlExecute("SELECT SCHOOL FROM CLASSES WHERE CLASSNO='{$_GET['CLASSNO']}'");

        if($one['SCHOOL']!=$classSCHOOL&&$one['SCHOOL']!='A1'){
            exit('您不能替其他学院的操作');
        }
        //todo:查询出该班级的学生
        $studentList=$this->md->sqlQuery("SELECT STUDENTNO FROM STUDENTS WHERE CLASSNO='{$_POST['CLASSNO']}'");

        //todo:查询有非必修课吗
        $three=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Four_Tongyi_three_fei.SQL'),array(':CLASSNO'=>$_POST['CLASSNO'],':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));


        //todo:没有必修课
        if(!$three){

            foreach($studentList as $val){

                //todo:查询 出学生在TIMELIST 表中的信息
                $student=$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_Tongyi_four.SQL'),array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':WHO'=>$val['STUDENTNO']));
                //todo:删除 学生在TIMELIST表中的信息
                $del=$this->md->sqlExecute($this->md->getSqlMap($this->base.'Four_Tongyi_deleteTimeList.SQL'),array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':WHO'=>$val['STUDENTNO']));
                //todo:往  TIMELIST表中插入信息
                $this->md->sqlExecute($this->md->getSqlMap($this->base.'Four_Tongyi_insertTimeList.SQL'),
                    array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':WHO'=>$val['STUDENTNO'],':TYPE'=>'S',':PARA'=>'',':MON'=>$student['MON'],':TUE'=>$student['TUE'],':WES'=>$student['WES'],
                        ':THU'=>$student['THU'],':FRI'=>$student['FRI'],':SAT'=>$student['SAT'],':SUN'=>$student['SUN']));
            }
            $str.='操作成功，请学生进入自己的选课单查看所选课程情况。';
        }else{      //todo:有必修课


            /*  foreach($three as $key=>$val){
                  $course_no=substr($val['COURSENOGROUP'],0,7);        //todo:7位
                  $course_no2=substr($val['COURSENOGROUP'],0,6);       //todo:6位
                  $gp=substr($val['COURSENOGROUP'],7);
                  //todo:查询出课程的TimeList
                  $courseTimeList=$this->md->sqlFind("SELECT MON,TUE,WES,THU,FRI,SAT,SUN FROM TIMELIST WHERE (YEAR={$_POST['YEAR']}) AND(TERM={$_POST['TERM']}) AND(WHO='{$course_no}') AND(TYPE='P') AND (PARA='$gp')");
              }*/
            $start2=time();
            foreach($studentList as $val){

                //todo:查询 出学生在TIMELIST 表中的信息
                $student=$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_Tongyi_four.SQL'),array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':WHO'=>$val['STUDENTNO']));
                $start=time();
                //todo:循环必修课
                foreach($three as $j){
                    $conflicts=0;
                    $repeat=0;
                    $fee=0;



                    //todo: 判断人数有超吗
                    $num=$this->md->sqlFind("SELECT LOCK,HALFLOCK,ATTENDENTS,ESTIMATE FROM SCHEDULEPLAN WHERE YEAR={$_GET['YEAR']} AND TERM={$_GET['TERM']} AND RTRIM(COURSENO)+RTRIM([GROUP])='{$j['COURSENOGROUP']}'");

                    if($num['ATTENDENTS']>=$num['ESTIMATE']){        //todo:如果人数超了
                        $str.=$j['COURSENOGROUP'].'选课人数超过预计人数或教室座位数限制，请调整教室或预计人数！<br>';
                        if($num['ATTENDENTS']-$num['ESTIMATE']>5){
                            $str.='课程:'.$j['COURSENOGROUP'].'的选课人数已超过预计人数的5人以上，被确认的可能性较低，请通知同学检查他的选课表注意选课确认情况！<br>';
                        }
                    }

                    $course_no=substr($j['COURSENOGROUP'],0,7);        //todo:7位
                    $course_no2=substr($j['COURSENOGROUP'],0,6);       //todo:6位
                    $gp=substr($j['COURSENOGROUP'],7);
                    //todo:查询出课程的TimeList
                    $courseTimeList=$this->md->sqlFind("SELECT MON,TUE,WES,THU,FRI,SAT,SUN FROM TIMELIST WHERE (YEAR={$_GET['YEAR']}) AND(TERM={$_GET['TERM']}) AND(WHO='{$course_no}') AND(TYPE='P') AND (PARA='$gp')");


                    //todo:怎么判断 012W21B00和他已选的课程上课时间有冲突，建议他进入退选程序，适当退选课程。   &吗？
                    foreach($courseTimeList as $key=>$s){

                        if($s&$student[$key]){
                            $str.=$val['STUDENTNO'].'所选的课程'. $j['COURSENOGROUP']."和他已选的课程上课时间有冲突，建议他进入退选程序，适当退选课程。<br>";
                            $conflicts=1;
                            break;
                        }
                    }

                    if(!$conflicts){
                        //todo:更新TimeList
                        $val['MON']|=$courseTimeList['MON'];
                        $val['TUE']|=$courseTimeList['TUE'];
                        $val['WES']|=$courseTimeList['WES'];
                        $val['THU']|=$courseTimeList['THU'];
                        $val['FRI']|=$courseTimeList['FRI'];
                        $val['SAT']|=$courseTimeList['SAT'];
                        $val['SUN']|=$courseTimeList['SUN'];
                    }

                    //todo:查询  COURSETYPE EXAMTYPE
                    $courseType=$this->md->sqlFind("SELECT COURSENO,COURSETYPE,EXAMTYPE FROM VIEWSTUDENTPLANCOURSE WHERE STUDENTNO=:studentno AND COURSENO LIKE '$course_no2'",array(':studentno'=>$val['STUDENTNO']));

                    //todo:判断成绩单是否有了
                    $chengjidan=$this->md->sqlFind("SELECT COURSENO FROM SCORES WHERE STUDENTNO='{$val['STUDENTNO']}' AND COURSENO LIKE '{$course_no2}'");

                    if($chengjidan){
                        $str.=$val['STUDENTNO'].'所选的课程'.$j['COURSENOGROUP'].'已经包含在他的成绩单中，他需要交纳重修费才能重修此课程。<br>';
                        $repeat=1;
                    }

                    //todo:往R32插入
                    $r32=$this->md->sqlExecute($this->md->getSqlMap($this->base.'bixiu_insert32.SQL'),
                        array(':YEAR'=>$_GET['YEAR'],':TERM'=>$_GET['TERM'],':COURSENO'=>$course_no,':[GROUP]'=>$gp,':STUDENTNO'=>$val['STUDENTNO'],
                            ':INPROGRAM'=>1,':CONFLICTS'=>$conflicts,':REPEAT'=>$repeat,':FEE'=>$fee,':COURSETYPE'=>$courseType['COURSETYPE'],':EXAMTYPE'=>$courseType['EXAMTYPE']));

                    if(!$r32){
                        $str.=$val['STUDENTNO'].'在向数据库提交课程'. $j['COURSENOGROUP']."时发生异常，这门课程可能已经在他的选课单中了！<br>";
                        continue;
                    }



                    //todo:更新scheduleplan人数
                    $this->md->sqlExecute("UPDATE SCHEDULEPLAN SET ATTENDENTS=ATTENDENTS+1 WHERE YEAR={$_GET['YEAR']} AND TERM={$_GET['TERM']} AND COURSENO='$course_no' AND [GROUP]='$gp'");





                }

                //todo:删除学生TIMELIST
                $this->md->sqlExecute("DELETE TIMELIST WHERE YEAR={$_GET['YEAR']} AND TERM={$_GET['TERM']} AND WHO='{$val['STUDENTNO']}' AND TYPE='S'");



                //todo:重新插入学生TIMELIST;
                $this->md->sqlExecute("INSERT INTO TIMELIST (YEAR,TERM,WHO,TYPE,PARA,MON,TUE,WES,THU,FRI,SAT,SUN)
               VALUES({$_GET['YEAR']},{$_GET['TERM']},'{$val['STUDENTNO']}','S','',{$val['MON']},{$val['TUE']},{$val['WES']},{$val['THU']},{$val['FRI']},{$val['SAT']},{$val['SUN']})");
            }
            $end2=time();
            echo $end2-$start2.'秒';

        }

        echo $str;
        $this->assign('content',$str);
        $this->display();

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

//todo:一天有几节课
function countOneDay($v1, $v2){
    if(!$v1) $v1 = array();
    if($v2['UNIT']=="1") $v1[]=$v2["NAME"];
    return $v1;
}
?>