<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 14-6-13
 * Time: 上午9:11
 */
class IndexAction extends RightAction {
    private $model;
    protected $message = array("type"=>"info","message"=>"","dbError"=>"");

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }

    public function index(){
        $this->assign("yearTerm",$this->model->sqlFind("select * from YEAR_TERM where [TYPE]='E'"));
        $this->__done("index");
    }

    /**
     * 设置考试学年学期
     */
    public function config(){
        $bind = $this->model->getBind("YEAR,TERM", $_REQUEST);
        $data = $this->model->sqlExecute("update YEAR_TERM set YEAR=:YEAR,TERM=:TERM where [TYPE]='E'", $bind);
        if($data===false){
            $this->message["type"] = "error";
            $this->message["message"] = "<font color='red'>设置考试学年学期时发生错误</font>";
        }else $this->message["message"] = "设置考试学年学期成功";

        $this->ajaxReturn($this->message,"JSON");
        exit;
    }
}