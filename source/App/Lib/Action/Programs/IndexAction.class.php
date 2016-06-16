<?php
/**
 * 教学计划
 * User: cwebs
 * Date: 14-2-23
 * Time: 上午8:47
 */
class IndexAction extends RightAction {
    private $model;
    private $message = array("type"=>"info","message"=>"","dbError"=>"");
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
     * 教学计划首页
     */
    public function index(){
        $this->display();
    }

    /**
     * 新建教学计划
     */
    public function create(){
        $this->display();
    }

    public function newSave(){
        if(!$_REQUEST["PROGRAMNO"] || !$_REQUEST["PROGRAMNAME"] || !$_REQUEST["DATE"] || !$_REQUEST["VALID"] || !$_REQUEST["SCHOOL"] || !$_REQUEST["TYPE"]){
            $this->message["type"] = "error";
            $this->message["message"] = "输入的参数有错误，非法提交数据！";
        }else if($this->theacher["SCHOOL"]!=$_REQUEST["SCHOOL"]){
            $this->message["type"] = "error";
            $this->message["message"] = "不要试图添加别的学院的教学计划！！";
        }else{
            $bind = $this->model->getBind("PROGRAMNO,PROGRAMNAME,DATE,REM,URL,VALID,SCHOOL,TYPE", $_REQUEST, "");
            $data = $this->model->sqlExecute($this->model->getSqlMap("Programs/newSave.sql"), $bind);
            if($data===false){
                $this->message["type"] = "error";
                $this->message["message"] = "<font color='red'>创建教学计划[".$_REQUEST["PROGRAMNO"]."]时发生错误</font>";
            }else $this->message["message"] = "创建教学计划[".$_REQUEST["PROGRAMNO"]."]成功";
        }

        $this->assign("hashNewSave",true);
        $this->assign("message",$this->message);
        $this->display("create");
    }
}