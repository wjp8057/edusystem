<?php
/**
 * 学生首页模块
 * User: educk
 * Date: 13-12-25
 * Time: 下午2:59
 */
class IndexAction extends RightAction {
    private $model;

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }

    /**
     * [ACT]学生系统首页
     */
    public function index(){

        $data = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"S"));
        $this->assign("yearTerm",$data);
        $this->display();
    }

    /**
     * {ACT]修改密码
     */
    public function changePwd(){
        if($this->_hasJson){
            $message = array("message"=>"","type"=>"info");
            if(!$_REQUEST["oldPwd"] || !$_REQUEST["newPwd"]){
                $message["message"] = "缺少必要的参数！";
                $message["type"] = "error";
            }elseif(trim($_SESSION["S_USER_INFO"]["PASSWORD"])!=$_REQUEST["oldPwd"]){
                $message["message"] = "修改密码时检测到原密码错误！";
                $message["type"] = "error";
            }else{
                $sql=""; $bind=array();
                $type=$_SESSION["S_LOGIN_TYPE"];
                if($type=="1"){
                    $sql="update USERS set PASSWORD=:PASSWORD where USERNAME=:USERNAME";
                    $bind=array(":PASSWORD"=>$_REQUEST["newPwd"],":USERNAME"=>session("S_USER_NAME"));
                }else if($type=="2"){
                    $sql="update STUDENTS set PASSWORD=:PASSWORD where STUDENTNO=:STUDENTNO";
                    $bind=array(":PASSWORD"=>$_REQUEST["newPwd"],":STUDENTNO"=>session("S_USER_NAME"));
                }
                $data = $this->model->sqlExecute($sql,$bind);
                if($data===false  || $data==0){
                    $message["message"] = "更新密码时发错误，更新密码失败！";
                    $message["type"] = "error";
                }else{
                    $message["message"] = "密码已成功更新，下次登陆时请使用新密码！";
                    $_SESSION["S_USER_INFO"]["PASSWORD"] = $_REQUEST["newPwd"];
                }
            }

            $this->ajaxReturn($message,"JSON");
            exit;

        }
    }
}