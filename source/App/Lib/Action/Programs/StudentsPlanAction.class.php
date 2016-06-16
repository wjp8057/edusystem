<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 14-4-26
 * Time: 上午9:15
 */
class StudentsPlanAction extends RightAction {
    private $model;
    private $message = array("type"=>"info","message"=>"","dbError"=>"");
    private $theacher;

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");

        $user = session("S_USER_INFO");
        $bind = $this->model->getBind("TEACHERNO", $user["TEACHERNO"]);
        $sql = $this->model->getSqlMap("user/teacher/editUser.SQL");
        $this->theacher = $this->model->sqlFind($sql, $bind);
        $this->assign("theacher", $this->theacher);
    }

    public function search(){
        if($this->_hasJson){
            $_REQUEST["STATUS"] = "%";
            $json = array("total"=>0, "rows"=>array());
            $bind = $this->model->getBind("STUDENTNO,NAME,CLASSNO,SCHOOL,STATUS",$_REQUEST);
            $sql = $this->model->getSqlMap("status/statusQueryStudents_Count.sql");
            $count = $this->model->sqlCount($sql, $bind);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getSqlMap("status/statusQueryStudents_ByClassno.sql");
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->display("search");
    }

    public function alist(){
        $bind = $this->model->getBind("STUDENTNO",$_REQUEST);
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Students/queryR28ByStudentCount.sql");
            $count = $this->model->sqlCount($sql, $bind);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getSqlMap("Students/queryR28ByStudent.sql");
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $sql = $this->model->getSqlMap("Students/queryStudent.sql");
        $this->assign("student", $this->model->sqlFind($sql, $bind));
        $this->display("alist");
    }

    public function add(){
        $bind = $this->model->getBind("PROGRAMNO,PROGRAMNAME,SCHOOL",$_REQUEST);
        $json = array("total"=>0, "rows"=>array());
        $sql = $this->model->getSqlMap("Class/QueryClassProgramCount.sql");
        $count = $this->model->sqlCount($sql, $bind);
        $json["total"] = intval($count);

        if($json["total"]>0){
            $sql = $this->model->getSqlMap("Class/QueryClassProgram.sql");
            $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
            $json["rows"] = $this->model->sqlQuery($sql, $bind);
        }
        $this->ajaxReturn($json,"JSON");
    }

    public function save(){
        if(!$_REQUEST["STUDENTNO"] || !is_array($_REQUEST["PROGRAMNO"]) || count($_REQUEST["PROGRAMNO"])==0){
            $this->message["message"] = "没有提交任一数据\n";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        $bind = $this->model->getBind("STUDENTNO",$_REQUEST);
        $data = $this->model->sqlFind($this->model->getSqlMap("Students/queryStudent.sql"), $bind);
        if($data==null || $data["SCHOOL"]!=$this->theacher["SCHOOL"]){
            $this->message["type"] = "error";
            $this->message["message"] = "这个学生的班级不属于你们学院，你不能对其修课计划作改动！";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        foreach($_REQUEST["PROGRAMNO"] as $PROGRAMNO){
            if($PROGRAMNO){
                $bind = $this->model->getBind("STUDENTNO,PROGRAMNO",$_REQUEST["STUDENTNO"].",".$PROGRAMNO);
                $data = $this->model->sqlExecute("INSERT INTO R28 (STUDENTNO,PROGRAMNO) values (:STUDENTNO,:PROGRAMNO)",$bind);
                if($data===false){
                    $this->message["type"] = "error";
                    if(strpos($this->model->getDbError(), "PRIMARY KEY 约束")){
                        $this->message["message"] .= "<font color='red'>教学计划号[".$PROGRAMNO."]已存在！</font>\n";
                    }else{
                        $this->message["dbError"] .= $this->model->getDbError();
                    }
                }else $this->message["message"] .= "教学计划号[".$PROGRAMNO."]添加成功！\n";
            }
        }
        $this->message["message"] = nl2br($this->message["message"]);
        $this->ajaxReturn($this->message,"JSON");
        exit;
    }

    public function delete(){
        if(!$_REQUEST["STUDENTNO"] || !is_array($_REQUEST["PROGRAMNO"]) || count($_REQUEST["PROGRAMNO"])==0){
            $this->message["message"] = "没有提交任一数据\n";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        $bind = $this->model->getBind("STUDENTNO",$_REQUEST);
        $data = $this->model->sqlFind($this->model->getSqlMap("Students/queryStudent.sql"), $bind);
        if($data==null || $data["SCHOOL"]!=$this->theacher["SCHOOL"]){
            $this->message["type"] = "error";
            $this->message["message"] = "这个学生的班级不属于你们学院，你不能对其修课计划作删除！";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        foreach($_REQUEST["PROGRAMNO"] as $PROGRAMNO){
            if($PROGRAMNO){
                $bind = $this->model->getBind("STUDENTNO,PROGRAMNO",$_REQUEST["STUDENTNO"].",".$PROGRAMNO);
                $data = $this->model->sqlExecute("delete from R28 where StudentNo=:STUDENTNO and ProgramNo=:PROGRAMNO",$bind);
                if($data===false){
                    $this->message["type"] = "error";
                    $this->message["message"] .= "<font color='red'>教学计划号[".$PROGRAMNO."]删除时发生错误！</font>\n";
                    $this->message["dbError"] .= $this->model->getDbError();
                }else $this->message["message"] .= "教学计划号[".$PROGRAMNO."]删除成功！\n";
            }
        }
        $this->message["message"] = nl2br($this->message["message"]);
        $this->ajaxReturn($this->message,"JSON");
        exit;
    }
} 