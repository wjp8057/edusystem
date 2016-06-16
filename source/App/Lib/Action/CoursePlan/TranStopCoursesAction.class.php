<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 14-5-22
 * Time: 下午2:13
 */
class TranStopCoursesAction extends RightAction{
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
     * 调停课申请
     */
    public function apply(){
        if($this->_hasJson){
            $arr = array("total"=>0, "rows"=>array());
            $total=$this->model->sqlfind($this->model->getSqlMap("coursePlan/scheduleplanChangeSubmitResult_count.sql"),
                array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':COURSENO'=>doWithBindStr($_POST['COURSENO']),':TEACHERNAME'=>doWithBindStr($_POST['TEACHERNAME']),":SCHOOL"=>doWithBindStr($_POST['SCHOOL'])));
            $arr["total"]=intval($total['ROWS']);
            if( $arr["total"] >  0){
                $arr["rows"] = $this->model->sqlQuery($this->model->getSqlMap("coursePlan/scheduleplanChangeSubmitResult.sql"),
                    array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':COURSENO'=>doWithBindStr($_POST['COURSENO']),':TEACHERNAME'=>doWithBindStr($_POST['TEACHERNAME']),":SCHOOL"=>doWithBindStr($_POST['SCHOOL']),':start'=>$this->_pageDataIndex+1,':end'=>$this->_pageDataIndex+$this->_pageSize));
            }
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
        $school = $this->model->sqlFind("SELECT * FROM SCHOOLS WHERE SCHOOL = (SELECT T.SCHOOL FROM TEACHERS T WHERE T.TEACHERNO=:TEACHERNO)",array(":TEACHERNO"=>session('S_TEACHERNO')));
        $this->assign("userSchool",$school);
        $this->assign("school",M("schools")->select());
        $this->__done("apply");
    }

    public function doApply(){

        //todo: 检测传入的参数
        if(VarIsSet("REASON,WEEK,ITEMS")==false || is_array($_REQUEST["ITEMS"])==false || count($_REQUEST["ITEMS"])==0){
            $this->message["type"] = "error";
            $this->message["message"] = "输入的参数有错误，非法提交数据！";
            $this->__done();
        }
        $school=$this->theacher["SCHOOL"];
        $school=$school=="A1"?"%":$school;
        $bind = $this->model->getBind("REASON,WEEK,USERNAME,ADDTION,SCHOOL",
            array($_REQUEST["REASON"],$_REQUEST["WEEK"],session("S_USER_NAME"),$_POST['date'],doWithBindStr($school)));
        $sql = $this->model->getSqlMap("coursePlan/tranAndStopScheduleChangeApply.sql");
        $sql = str_replace(":RECNOS",implode(",",$_REQUEST["ITEMS"]),$sql);

        $data = $this->model->sqlExecute($sql,$bind);
        //var_dump($data);
        $this->message["type"] = "info";
        if(!intval($data)){
            $this->message["message"]='该申请已经提交过了。';
        }else{
            $this->message["message"] = intval($data)."条记录申请调停课成功！";
        }
        $this->__done();
    }

    /**
     * 学院领导审批
     */
    public function schoolApproval(){
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $school=$this->theacher["SCHOOL"];
            $school=$school=="A1"?"%":$school;
            $bind = $this->model->getBind("YEAR,TERM,SCHOOL",array($_REQUEST['YEAR'],$_REQUEST['TERM'],doWithBindStr($school)));
            $json["rows"] = $this->model->sqlQuery($this->model->getSqlMap("coursePlan/scheduleplanChangeVerify.sql"), $bind);
            $this->ajaxReturn($json["rows"],"JSON");
            exit;
        }
        $this->__done("schoolapproval");
    }
    public function doVerify(){
        //todo: 检测传入的参数
        if(VarIsSet("RIGHT,ITEMS")==false || is_array($_REQUEST["ITEMS"])==false || count($_REQUEST["ITEMS"])==0){
            $this->message["type"] = "error";
            $this->message["message"] = "输入的参数有错误，非法提交数据！";
            $this->__done();
        }
        if(intval($_REQUEST["RIGHT"])==1){
            $bind = $this->model->getBind("USERNAME,SCHOOL",array(session("S_USER_NAME"),$this->theacher["SCHOOL"]));
            $sql = "update ScheduleChange set Verify=:USERNAME,VerifyDate=getdate() where RecNo in (:RECNO)";
            if($this->theacher["SCHOOL"] !="A1")$sql.=" and school=:SCHOOL";
        }else{
            $bind = $this->model->getBind("SCHOOL",$this->theacher["SCHOOL"]);
            $sql = "delete from ScheduleChange where RecNo in (:RECNO)";
            if($this->theacher["SCHOOL"] !="A1")$sql.=" and school=:SCHOOL";
        }
        $sql = str_replace(":RECNO",implode(",",$_REQUEST["ITEMS"]),$sql);
        $data = $this->model->sqlExecute($sql,$bind);
        $this->message["type"] = "info";
        $this->message["message"] = intval($data)."条记录进行了调停课审核！";
        $this->__done();
    }

    /**
     * 审批进度查询
     */
    public function progress(){
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $bind = $this->model->getBind("YEAR,TERM,COURSENO,SCHOOL",$_REQUEST);
            $json["total"] = $this->model->sqlCount($this->model->getSqlMap("coursePlan/scheduleplanChangeQueryResult.sql"), $bind, true);
            if($json["total"]>0){
                $sql = $this->model->getPageSql($this->model->getSqlMap("coursePlan/scheduleplanChangeQueryResult.sql"),null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }

        $teachername=$this->model->sqlfind("select NAME FROM TEACHERS where TEACHERNO='{$_SESSION['S_USER_INFO']['TEACHERNO']}'");

        $this->assign('teachername',$teachername['NAME']);
        $this->__done("progress");
    }

    //删除progress页面中数据的方法
    public function progress_del(){

            $this->model->startTrans();
        foreach($_POST['bind'] as $val){

            $int=$this->model->sqlExecute("delete from schedulechange where recno='{$val['RECNO']}'");
            if(!$int){
                $this->model->rollback();
                exit('系统错误!');
            }
        }
    $this->model->commit();
        exit('删除成功');
    }

    /**
     *教务处领导审批
     */
    public function registryApproval(){
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $bind = $this->model->getBind("YEAR,TERM",array($_REQUEST['YEAR'],$_REQUEST['TERM']));
            $json["rows"] = $this->model->sqlQuery($this->model->getSqlMap("coursePlan/scheduleplanChangeApprove.sql"), $bind);
            $this->ajaxReturn($json["rows"],"JSON");
            exit;
        }
        $this->__done("registryapproval");
    }
    public function doApprove(){
        //todo: 检测传入的参数
        if(VarIsSet("RIGHT,ITEMS")==false || is_array($_REQUEST["ITEMS"])==false || count($_REQUEST["ITEMS"])==0){
            $this->message["type"] = "error";
            $this->message["message"] = "输入的参数有错误，非法提交数据！";
            $this->__done();
        }elseif($this->theacher['SCHOOL']!="A1"){
            $this->message["type"] = "error";
            $this->message["message"] = "您不是教务处管理人员，无法进行审批！";
            $this->__done();
        }

        if(intval($_REQUEST["RIGHT"])==1){
            $sql = "update ScheduleChange set Approve=:USERNAME,ApproveDate=getdate(),Enable=1 where RecNo in (:RECNO)";
        }else{
            $sql = "update scheduleChange set Approve=:USERNAME,ApproveDate=getdate(),enable=0 where recno in (:RECNO)";
        }
        $sql = str_replace(":RECNO",implode(",",$_REQUEST["ITEMS"]),$sql);
        $bind = $this->model->getBind("USERNAME",session("S_USER_NAME"));
        $data = $this->model->sqlExecute($sql,$bind);
        $this->message["type"] = "info";
        $this->message["message"] = intval($data)."条记录进行了调停课审批！";
        $this->__done();
    }

    /**
     * 补课查询与输入
     */
    public function makeup(){
        if($this->_hasJson){
        	
            $json = array("total"=>0, "rows"=>array());
            $bind = $this->model->getBind("YEAR,TERM,SCHOOL,COURSENO,TEACHERNAME",$_REQUEST);
            $json["total"] = $this->model->sqlCount($this->model->getSqlMap("coursePlan/scheduleplanAddQueryResultCount.sql"), $bind);
            if($json["total"]>0){
                $sql = $this->model->getPageSql($this->model->getSqlMap("coursePlan/scheduleplanAddQueryResult.sql"),null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->__done("makeup");
    }
    public function makeupUpdate(){
        //todo: 检测传入的参数
        if(VarIsSet("ADDTION,RECNO")==false){
            $this->message["type"] = "error";
            $this->message["message"] = "输入的参数有错误，非法提交数据！";
            $this->__done();
        }

        $bind = $this->model->getBind("ADDTOR,ADDTION,RECNO,SCHOOL",array(session("S_USER_NAME"),$_REQUEST["ADDTION"],$_REQUEST["RECNO"],$this->theacher["SCHOOL"]));
        $sql = "update schedulechange set addtor=:ADDTOR,addtion=:ADDTION from ScheduleChange WHERE RECNO=:RECNO";
        if($this->theacher["SCHOOL"] !="A1")$sql.=" and School=:SCHOOL";
        $data = $this->model->sqlExecute($sql,$bind);
        if($data===false || $data==0){
            $this->message["type"] = "error";
            $this->message["message"] = "<font color='red'>补课记录更新时，发生错误！</font>";
        }else{
            $this->message["type"] = "info";
            $this->message["message"] = "补课记录更新成功！";
        }
        $this->__done();
    }
    public function makeupDelete(){
        if($this->theacher["SCHOOL"]!="A1"){
            $this->message["type"] = "error";
            $this->message["message"] = "<font color='red'>您不是教务处管理人员，无法删除调课记录！</font>";
            $this->__done();
        }elseif(is_array($_REQUEST['ITEMS'])==false || count($_REQUEST['ITEMS'])==0){
                $this->message["type"] = "error";
                $this->message["message"] = "输入的参数有错误，非法提交数据！";
                $this->__done();
        }
        $sql = "delete from schedulechange where recno in (".implode(",",$_REQUEST['ITEMS']).")";
        $data = $this->model->sqlExecute($sql);
        if($data===false || $data==0){
            $this->message["type"] = "error";
            $this->message["message"] = "<font color='red'>补课记录删除时，发生错误！</font>";
        }else{
            $this->message["type"] = "info";
            $this->message["message"] = $data."条补课记录删除成功！";
        }
        $this->__done();
    }
}
