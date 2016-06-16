<?php
/**
* 开课计划
* User: cwebs
* Date: 14-2-23
* Time: 上午8:47
*/
class TimetableAction extends RightAction {
    private $model;
    protected $message = array("type"=>"info","message"=>"","dbError"=>"");
    private $theacher;

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");

        $bind = $this->model->getBind("SESSIONID", session("S_GUID"));
        $sql = $this->model->getSqlMap("user/teacher/getUserBySessionId.sql");
        $this->theacher = $this->model->sqlFind($sql, $bind);
        $this->assign("theacher", $this->theacher);
    }

    /**
     * [PL06]自动创建排课计划
     */
    public function auto(){
        if($this->_hasJson){

            $this->model->startTrans();
            //todo: 检测传入的参数
            if(VarIsIntval("YEAR,TERM")==false){
                $this->message["type"] = "error";
                $this->message["message"] = "输入的参数有错误，非法提交数据！";
                $this->__done();
            }
            $bind = $this->model->getBind("YEAR1,TERM1,YEAR2,TERM2,YEAR3,TERM3,YEAR4,TERM4",
                array($_REQUEST["YEAR"],$_REQUEST["TERM"],
                    $_REQUEST["YEAR"],$_REQUEST["TERM"],
                    $_REQUEST["YEAR"],$_REQUEST["TERM"],
                    $_REQUEST["YEAR"],$_REQUEST["TERM"],
                    ));
            $data = $this->model->sqlExecute($this->model->getSqlMap("coursePlan/transferGate.sql"),$bind);

            $bind = $this->model->getBind("YEAR5,TERM5",array($_REQUEST["YEAR"],$_REQUEST["TERM"]));
            $data = $this->model->sqlExecute($this->model->getSqlMap("coursePlan/transferGate2.sql"),$bind);
            if($data===false){

                $this->message["type"] = "error";
                $this->message["message"] =iconv('gb2312','utf-8',substr($this->model->getDbError(),0,strpos($this->model->getDbError(),'[ SQL语句 ]')));

                $this->model->rollback();
                 $this->__done();
            }

            $bind = $this->model->getBind("YEAR,TERM",array($_REQUEST["YEAR"],$_REQUEST["TERM"]));
            $data = $this->model->sqlCount("SELECT count(*) FROM SCHEDULEPLAN WHERE YEAR=:YEAR AND TERM=:TERM",$bind);
            $this->message["type"] = "info";
            $this->message["message"] = $data."条纪录被传送到排课计划表！！";
            $this->model->commit();
            $this->__done("auto");

        }
        $this->__done("auto");
    }

    /**
     * [PL06]给课程添加未定教师
     */
    public function  addUnknow(){
        //todo: 检测传入的参数
        if(VarIsIntval("YEAR,TERM")==false){
            $this->message["type"] = "error";
            $this->message["message"] = "输入的参数有错误，非法提交数据！";
            $this->__done();
        }
        $bind = $this->model->getBind("YEAR,TERM",$_REQUEST);
        $data = $this->model->sqlExecute($this->model->getSqlMap("coursePlan/addUnknowTheacher.sql"),$bind);
        if($data===false){
            $this->model->rollback();
            $this->message["type"] = "error";
            $this->message["message"] = "给课程添加未定教师发生错误！！";
            $this->__done();
        }
        $this->message["type"] = "info";
        $this->message["message"] = "给课程添加未定教师成功！！";
        $this->__done("auto");
    }

    /**
     * [PL01]排课计划查询页
     */
    public function qform(){
        $this->__done("qform");
    }

    /**
     * [PL01]排课计划列表页
     */
    public function qlist(){
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $bind = $this->model->getBind("YEAR,TERM,COURSENO,GROUP,SCHOOL,COURSETYPE,SCHEDULED,ROOMTYPE,CLASSNO,EXAMTYPE,ESTIMATEUP,ESTIMATEDOWN,ATTENDENTSUP,ATTENDENTSDOWN,DAYS",$_REQUEST,"%");
            $sql = $this->model->getSqlMap("coursePlan/QueryScheduleplanCount.sql");
            $count = $this->model->sqlCount($this->formatScheduleplanSQL($sql), $bind);
            $json["total"] = intval($count);


//                varsPrint($this->model->getLastSql(),$bind);

            if($json["total"]>0){
                $sql = $this->model->getSqlMap("coursePlan/QueryScheduleplan.sql");

                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($this->formatScheduleplanSQL($sql), $bind);
                foreach($json["rows"] as $k=>$row){
                    $json["rows"][$k]["WEEKS"] = strrev(sprintf("%018s", decbin($row["WEEKS"])));
                }
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }


        $this->assign("queryParams",count($_REQUEST)>0?json_encode($_REQUEST):"{}");
        $this->__done("qlist");
    }


    //todo:导出excel
    public function courseplan_one(){
        echo '<pre>';
        print_r($_REQUEST);
        exit;
        $bind = $this->model->getBind("YEAR,TERM,COURSENO,GROUP,SCHOOL,COURSETYPE,SCHEDULED,ROOMTYPE,CLASSNO,EXAMTYPE,ESTIMATEUP,ESTIMATEDOWN,ATTENDENTSUP,ATTENDENTSDOWN,DAYS",$_REQUEST,"%");
        $sql = $this->model->getSqlMap("coursePlan/QueryScheduleplan.sql");
        $arr = $this->model->sqlQuery($this->formatScheduleplanSQL($sql), $bind);
        echo '<pre>';
        print_r($arr);

    }

    private function  formatScheduleplanSQL($sql){
        if(isset($_REQUEST["LOCK"]) && ($_REQUEST["LOCK"]==1 || $_REQUEST["LOCK"]==0))
            $sql = str_replace('{$SQL.LOCK}',"AND SCHEDULEPLAN.LOCK=".intval($_REQUEST["LOCK"]),$sql);
        else
            $sql = str_replace('{$SQL.LOCK}',"",$sql);
        if(isset($_REQUEST["EXAM"]) && ($_REQUEST["EXAM"]==1 || $_REQUEST["EXAM"]==0))
            $sql = str_replace('{$SQL.EXAM}',"AND SCHEDULEPLAN.EXAM=".intval($_REQUEST["EXAM"]),$sql);
        else
            $sql = str_replace('{$SQL.EXAM}',"",$sql);
        $searchType = intval($_REQUEST["SEARCHTYPE"]);
        if($searchType==2){
            $sql = str_replace('{$SQL.SRARCHTYPE}',"AND THETEACHERS.TEACHERNAME IS NULL",$sql);
        }elseif($searchType==3){
            $sql = str_replace('{$SQL.SRARCHTYPE}',"AND (THETEACHERS.TEACHERNAME IS NOT NULL AND L_ZC.JB='初级')",$sql);
        }elseif($searchType==4){
            $sql = str_replace('{$SQL.SRARCHTYPE}',"AND COURSEPLAN.CLASSNO LIKE '000000%'",$sql);
        }else{
            $sql = str_replace('{$SQL.SRARCHTYPE}',"AND (THETEACHERS.SCHOOL LIKE '".$_REQUEST["TEACHERNO"]."' OR THETEACHERS.SCHOOL IS NULL)",$sql);
        }
        return $sql;
    }

    /**
     * 更新排课计划
     */
    public function update(){

        if($this->_hasJson){

            $bind = $this->model->getBind("RECNO",$_REQUEST,"%");
            $data = $this->model->sqlFind($this->model->getSqlMap("coursePlan/SelectScheduleplanByRecNO.sql"), $bind);
            if($data==null || ($this->theacher["SCHOOL"]!=$data["SCHOOL"]&&$this->theacher['SCHOOL']!='A1')){
                $this->message["type"] = "error";
                $this->message["message"] = "你不可以更改别的学院开设的课程属性！";
                $this->__done();
            }
            $bind = $this->model->getBind("ESTIMATE,EBATCH,degree,EMPROOM,ROOMTYPE,REM,SEATSLOCK,TIME,SCHEDULED,LHOURS,EHOURS,CHOURS,KHOURS,SHOURS,ZHOURS,LOCK,EXAM,DAYS,AREA,RECNO",$_REQUEST);
            $data = $this->model->sqlExecute($this->model->getSqlMap("coursePlan/updateSchedulePlan.sql"),$bind);
            if($data===false){
                $this->message["type"] = "error";
                $this->message["message"] = "<font color='red'>更新失败！</font>";
                $this->__done();
            }
            $this->message["type"] = "info";
            $this->message["message"] = "更新成功！";
            $this->__done();
        }
        $bind = $this->model->getBind("YEAR,TERM,COURSENO,GROUP",$_REQUEST,"%");
        $data = $this->model->sqlFind($this->model->getSqlMap("coursePlan/SelectScheduleplan.sql"), $bind);
        if($data) {
            $data["WEEKS"] = strrev(sprintf("%018s", decbin($data["WEEKS"])));
        }
        $this->assign("schedulePlan",$data);
        $this->__done("update");
    }

    /**
     * 删除排课计划
     */
    public function delete(){
        if($this->theacher["SCHOOL"]!="A1"){
            $this->message["type"] = "error";
            $this->message["message"] = "只有教务处人员才可以删除开课记录！";
            $this->__done();
        }
        //todo: 检测传入的参数
        if(is_array($_REQUEST['ITEM'])==false || count($_REQUEST['ITEM'])==0){
            $this->message["type"] = "error";
            $this->message["message"] = "输入的参数有错误，非法提交数据！";
            $this->__done();
        }

        foreach($_REQUEST['ITEM'] as $row){
            $items = @explode(",",$row);
            if(count($items)!=4) continue;
            $year = intval($items[0]);
            $term = intval($items[1]);
            $courseno = trim($items[2]);
            $group = trim($items[3]);

            //todo: 排课计划是否存在
            $bind = $this->model->getBind("YEAR,TERM,COURSENO,GROUP", array($year,$term,$courseno,$group));
            $data = $this->model->sqlQuery("SELECT RECNO FROM SCHEDULEPLAN WHERE YEAR=:YEAR AND TERM=:TERM AND COURSENO=:COURSENO AND [GROUP]=:GROUP",$bind);
            if($data==null || count($data)!=1) {
                $this->message["message"] .= "<font color='red'>排课号[$courseno][$group]不存在！</font><br />";
                continue;
            }

            $this->model->startTrans();
            $strBind  = "YEAR1,TERM1,COURSENO1,GROUP1,YEAR2,TERM2,COURSENO2,GROUP2,RECNO3,RECNO4,";
            $strBind .= "YEAR5,TERM5,COURSENO5,GROUP5,YEAR6,TERM6,COURSENO6,GROUP6";
            $bind = $this->model->getBind($strBind,
                array(
                    $year, $term, $courseno,$group,$year, $term, $courseno,$group,$data[0]["RECNO"],$data[0]["RECNO"],
                    $year, $term, $courseno,$group,$year, $term, $courseno,$group));
            $data = $this->model->sqlExecute($this->model->getSqlMap("coursePlan/deleteSchedulePlan.sql"),$bind);
            if($data===false){
                $this->model->rollback();
                $this->message["message"] .= "<font color='red'>排课号[$courseno][$group]，删除失败！</font><br />";
                $this->model->commit();
                continue;
            }
            $this->model->commit();
            $this->message["message"] .= "排课号[$courseno][$group]，删除成功！<br />";
        }
        $this->message["type"] = "info";
        $this->__done();
    }

    /**
     * 编辑教师
     */
    public function teacher(){
    /*    echo '<pre>';
        print_r($_SESSION);*/
        $bind = $this->model->getBind("RECNO",$_REQUEST);
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $count = $this->model->sqlCount($this->model->getSqlMap("coursePlan/SchedulePlanCountTeachers.sql"), $bind);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getPageSql( $sql = $this->model->getSqlMap("coursePlan/SchedulePlanSelectTeachers.sql"),null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
        }
        $data = $this->model->sqlFind($this->model->getSqlMap("coursePlan/SelectScheduleplanByRecNO.sql"), $bind);

        $this->assign("schedulePlan",$data);

        if($data!=null){
         $data = $this->model->sqlQuery($this->model->getSqlMap("coursePlan/teachersBySchoolName.sql"), array(":SCHOOLNAME"=>doWithBindStr($data["SCHOOLNAME"])));
      //  $data = $this->model->sqlQuery($this->model->getSqlMap("coursePlan/teachersBySchoolName.sql"),array(':SCHOOL'=>));
            $this->assign("selfTeachers",$data);
        }
        $this->xiala('schools','schools');
        $this->assign("queryParams",count($_REQUEST)>0?json_encode($_REQUEST):"{}");
        $this->__done("teacher");
    }

    /**
     * 新增教师
     */
    public function teacherSave(){
        //todo: 检测传入的参数
        if(VarIsSet("YEAR,TERM,MAP,TEACHERNO,HOURS,UNIT,REM,TASK,TOSCHEDULE")==false){
            $this->message["type"] = "error";
            $this->message["message"] = "输入的参数有错误，非法提交数据！";
            $this->__done();
        }
        //todo 检测指定的教师是否存在
        $bind = $this->model->getBind("TEACHERNO",$_REQUEST,"%");
        $data = $this->model->sqlFind("SELECT SCHOOL FROM TEACHERS WHERE TEACHERNO=:TEACHERNO", $bind);

//没有职称则不显示
        $rst = $this->model->sqlQuery('SELECT * from POSITIONS WHERE [NAME] = (SELECT [POSITION] from TEACHERS WHERE TEACHERNO=:TEACHERNO)',$bind);
        if(!$rst){
            $this->message["type"] = "error";
            $this->message["message"] = "教师号不存在，或者该教师无职称！".$this->model->getDbError();
            $this->__done();
        }

        if($data==null){
            $this->message["type"] = "error";
            $this->message["message"] = "教师号[".$_REQUEST['TEACHERNO']."]不存在！";
            $this->__done();
        }
        //todo 插入教师
        $bind = $this->model->getBind("YEAR,TERM,MAP,TEACHERNO,HOURS,UNIT,REM,TASK,TOSCHEDULE",$_REQUEST);
        $sql  = "INSERT INTO TEACHERPLAN (YEAR,TERM,MAP,TEACHERNO,HOURS,UNIT,REM,TASK,TOSCHEDULE) VALUES ";
        $sql .= "(:YEAR,:TERM,:MAP,:TEACHERNO,:HOURS,:UNIT,:REM,:TASK,:TOSCHEDULE)";
        $data = $this->model->sqlExecute($sql,$bind);
        if($data===false){
            $this->message["type"] = "error";
            $this->message["message"] = "<font color='red'>教师号[".$_REQUEST['TEACHERNO']."]添加失败！</font>";
        }else{
            $this->message["type"] = "info";
            $this->message["message"] = "教师号[".$_REQUEST['TEACHERNO']."]添加成功！";
        }
        $this->__done();
    }

    /**
     * 更新教师
     */
    public function teacherUpdate(){
        //todo: 检测传入的参数
        if(VarIsSet("YEAR,TERM,MAP,TEACHERNO,HOURS,UNIT,REM,TASK,TOSCHEDULE,RECNO")==false){
            $this->message["type"] = "error";
            $this->message["message"] = "输入的参数有错误，非法提交数据！";
            $this->__done();
        }
        //todo 检测指定的教师是否存在
        $bind = $this->model->getBind("TEACHERNO",$_REQUEST,"%");
        $data = $this->model->sqlFind("SELECT SCHOOL FROM TEACHERS WHERE TEACHERNO=:TEACHERNO", $bind);
        if($data==null){
            $this->message["type"] = "error";
            $this->message["message"] = "教师号[".$_REQUEST['TEACHERNO']."]不存在！";
            $this->__done();
        }
        //todo 修改教师
        $bind = $this->model->getBind("TEACHERNO,HOURS,UNIT,REM,TASK,TOSCHEDULE,RECNO",$_REQUEST);
        $sql  = "UPDATE TEACHERPLAN SET TEACHERNO=:TEACHERNO,HOURS=:HOURS,UNIT=:UNIT,REM=:REM,TASK=:TASK,TOSCHEDULE=:TOSCHEDULE WHERE RECNO=:RECNO";
        $data = $this->model->sqlExecute($sql,$bind);
        if($data===false){
            $this->message["type"] = "error";
            $this->message["message"] = "<font color='red'>教师号[".$_REQUEST['TEACHERNO']."]添加失败！</font>";
        }else{
            $this->message["type"] = "info";
            $this->message["message"] = "教师号[".$_REQUEST['TEACHERNO']."]添加成功！";
        }
        $this->__done();
    }

    /**
     * 删除教师
     */
    public function teacherDelete(){
        if(!is_array($_REQUEST["ITEMS"]) || count($_REQUEST["ITEMS"])==0){
            $this->message["type"] = "error";
            $this->message["message"] = "输入的参数有错误，非法提交数据！";
            $this->__done();
        }

        $count = 0;
        foreach($_REQUEST["ITEMS"] as $recno){
            $bind = $this->model->getBind("RECNO",$recno);
            $data = $this->model->sqlExecute("DELETE TEACHERPLAN WHERE RECNO=:RECNO",$bind);
            if($data!==false) $count++;
        }

        $this->message["type"] = "info";
        $this->message["message"] = $count."条记录已成功删除！";
        $this->__done();
    }

    /**
     * 将选中记录传递到课程总表
     */
    public function transfer(){
        if(!is_array($_REQUEST["ITEMS"]) || count($_REQUEST["ITEMS"])==0){
            $this->message["type"] = "error";
            $this->message["message"] = "输入的参数有错误，非法提交数据！";
            $this->__done();
        }

        $count = 0;
        foreach($_REQUEST["ITEMS"] as $recno){
            $bind = $this->model->getBind("RECNO",$recno);
            $data = $this->model->sqlExecute($this->model->getSqlMap("coursePlan/insertTransfer.sql"),$bind);
            if($data!==false) $count++;
        }

        $this->message["type"] = "info";
        $this->message["message"] = $count."条记录传递到课程总表！";
        $this->__done();
    }



    public function gongxuanke(){


       $this->model->startTrans();
        $int=$this->model->sqlExecute($this->model->getSqlMap('coursePlan/New_gongxuanke.SQL'),
            array(':yone'=>$_POST['YEAR'],':tone'=>$_POST['TERM'],':zhong'=>'%'.$_POST['TERM'].'%',':ytwo'=>$_POST['YEAR'],':ttwo'=>$_POST['TERM']));

       if($int===false){

           $this->model->rollback();
           exit(iconv('gb2312','utf-8',substr($this->model->getDbError(),0,strpos($this->model->getDbError(),'[ SQL语句 ]'))));
       }else{
           var_dump($int);
           $this->model->commit();
           exit('导入成功共');
       }


    }




}