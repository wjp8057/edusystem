<?php
/**
 * 系统首页
 * User: cwebs
 * Date: 13-11-23
 * Time: 上午8:47
 */
class IndexAction extends RightAction {
    private $model;
    private $message = array("type"=>"info","message"=>"","dbError"=>"");
    private $theacher;
    /**
     * 班级管理首页
     */

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }
    public function index()
    {
        $this->assign("yearTerm",$this->model->sqlFind("select * from YEAR_TERM where [TYPE]='C'"));
        $this->display();
    }

    public function config(){
        $bind = $this->model->getBind("YEAR,TERM", $_REQUEST);
        $data = $this->model->sqlExecute("update YEAR_TERM set YEAR=:YEAR,TERM=:TERM where [TYPE]='S'", $bind);
        if($data===false){
            $this->message["type"] = "error";
            $this->message["message"] = "<font color='red'>设置开课计划学年学期时发生错误</font>";
        }else $this->message["message"] = "设置学年学期成功";

        $this->ajaxReturn($this->message,"JSON");
        exit;
    }


}