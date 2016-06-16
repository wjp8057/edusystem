<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 14-4-26
 * Time: 上午9:15
 */
class ClassesPlanAction extends RightAction {
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
            $json = array("total"=>0, "rows"=>array());
            $bind = $this->model->getBind("CLASSNO,CLASSNAME,SCHOOL",$_REQUEST);
            $sql = $this->model->getSqlMap("Class/QueryClassSelectCount.sql");
            $count = $this->model->sqlCount($sql, $bind);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getSqlMap("Class/QueryClassSelect.sql");
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->display("search");
    }

    public function alist(){
        $bind = $this->model->getBind("CLASSNO",$_REQUEST);
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Class/Queryr16ByClassCount.sql");
            $count = $this->model->sqlCount($sql, $bind);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getSqlMap("Class/Queryr16ByClass.sql");
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $sql = $this->model->getSqlMap("Class/QueryClass.sql");
        $this->assign("class", $this->model->sqlFind($sql, $bind));
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
        if(!$_REQUEST["CLASSNO"] || !is_array($_REQUEST["PROGRAMNO"]) || count($_REQUEST["PROGRAMNO"])==0){
            $this->message["message"] = "没有提交任一数据\n";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        $bind = $this->model->getBind("CLASSNO",$_REQUEST);
        $data = $this->model->sqlFind($this->model->getSqlMap("Class/QueryClass.sql"), $bind);
        if($data==null || $data["SCHOOL"]!=$this->theacher["SCHOOL"]){
            $this->message["type"] = "error";
            $this->message["message"] = "对不起，别试图更改别的学院班级的教学计划绑定！";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        foreach($_REQUEST["PROGRAMNO"] as $PROGRAMNO){
            if($PROGRAMNO){
                $bind = $this->model->getBind("PROGRAMNO,CLASSNO",$PROGRAMNO.",".$_REQUEST["CLASSNO"]);
                $data = $this->model->sqlExecute("INSERT INTO R16 (PROGRAMNO,CLASSNO) values (:PROGRAMNO,:CLASSNO)",$bind);
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
        if(!$_REQUEST["CLASSNO"] || !is_array($_REQUEST["PROGRAMNO"]) || count($_REQUEST["PROGRAMNO"])==0){
            $this->message["message"] = "没有提交任一数据\n";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        $bind = $this->model->getBind("CLASSNO",$_REQUEST);
        $data = $this->model->sqlFind($this->model->getSqlMap("Class/QueryClass.sql"), $bind);
        if($data==null || $data["SCHOOL"]!=$this->theacher["SCHOOL"]){
            $this->message["type"] = "error";
            $this->message["message"] = "对不起，别试图删除别的学院班级的教学计划绑定！";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        foreach($_REQUEST["PROGRAMNO"] as $PROGRAMNO){
            if($PROGRAMNO){
                $bind = $this->model->getBind("PROGRAMNO,CLASSNO",$PROGRAMNO.",".$_REQUEST["CLASSNO"]);
                $data = $this->model->sqlExecute("DELETE R16 WHERE PROGRAMNO=:PROGRAMNO AND CLASSNO=:CLASSNO",$bind);
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

    public function bind(){
        if(!$_REQUEST["CLASSNO"] || !is_array($_REQUEST["PROGRAMNO"]) || count($_REQUEST["PROGRAMNO"])==0){
            $this->message["message"] = "没有提交任一数据\n";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        $bind = $this->model->getBind("CLASSNO",$_REQUEST);
        $data = $this->model->sqlFind($this->model->getSqlMap("Class/QueryClass.sql"), $bind);
        if($data==null || $data["SCHOOL"]!=$this->theacher["SCHOOL"]){
            $this->message["type"] = "error";
            $this->message["message"] = "对不起，别试图更改别的学院班级的教学计划绑定！";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }


        foreach($_REQUEST["PROGRAMNO"] as $PROGRAMNO){
            if($PROGRAMNO){
                $bind = $this->model->getBind("D_PROGRAMNO,D_CLASSNO,I_PROGRAMNO,I_CLASSNO",$PROGRAMNO.",".$_REQUEST["CLASSNO"].",".$PROGRAMNO.",".$_REQUEST["CLASSNO"]);
                $data = $this->model->sqlExecute($this->model->getSqlMap("programs/teachingBind.sql"),$bind);
                if($data===false){
                    $this->message["type"] = "error";
                    $this->message["message"] .= "<font color='red'>教学计划号[".$PROGRAMNO."]绑定时发生错误！</font>\n";
                    $this->message["dbError"] .= $this->model->getDbError();
                }else $this->message["message"] .= "教学计划号[".$PROGRAMNO."]绑定成功！\n";
            }
        }
        $this->message["message"] = nl2br($this->message["message"]);
        $this->ajaxReturn($this->message,"JSON");
        exit;
    }
} 