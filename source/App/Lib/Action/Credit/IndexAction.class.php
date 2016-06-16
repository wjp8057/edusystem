<?php
/**
 * 系统首页
 * User: cwebs
 * Date: 13-11-23
 * Time: 上午8:47
 */
class IndexAction extends RightAction {
    /**
     * 课程管理首页
     */
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


    public function index()
    {
        $this->assign("yearTerm",$this->model->sqlFind("select * from YEAR_TERM where [TYPE]='R'"));

        $this->display();
    }


    public function config(){
        $bind = $this->model->getBind("YEAR,TERM", $_REQUEST);
        $data = $this->model->sqlExecute("update YEAR_TERM set YEAR=:YEAR,TERM=:TERM where [TYPE]='R'", $bind);
        if($data===false){
            $this->message["type"] = "error";
            $this->message["message"] = "<font color='red'>设置学分认定学年学期时发生错误</font>";
        }else $this->message["message"] = "设置学分认定学年学期成功";

        $this->ajaxReturn($this->message,"JSON");
        exit;
    }
}