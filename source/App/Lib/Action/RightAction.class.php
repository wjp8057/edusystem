<?php
/**
 * 验证Action先祖，需要权限验证的棋坛请继承此类
 *
 * User: educk
 * Date: 13-11-20
 * Time: 下午4:08
 */
class RightAction extends CommonAction{
	private $model;
    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
        
        $actionPath = getActionPath();
        // 检验登陆
        $this->checkLogin($actionPath);

        // 如果是学生刚跳转到学生首页
        if($actionPath=="Teacher/Index/index" && session("S_LOGIN_TYPE")==2){
        	$this->logs(1);
            redirect("Student/Index/index");
            exit;
        }

        $user = $this->model->sqlFind("select ROLES from USERS where USERNAME=? and lock=?",array(session('S_USER_NAME'),0));
        $role = trim($user['ROLES']);
        if(!$role ){
            $role = "S";
        }

        //检验权限
        $mid = checkRight($actionPath, $role, session("S_LOGIN_TYPE"));
        if(true!==$mid)
        {
          $status = 710;
            if($this->_hasJson || IS_AJAX)
            {
                if($mid===false) header("HTTP/1.0 ".$status." ".$actionPath." Not Found");
                else header("HTTP/1.0 ".$status." MID=".$mid.", ACT=".$actionPath." Not Permission");
            }
            //elseif(IS_AJAX){
                //20150928 AJAX返回 错误信息
                //$this->exitWithReport("'{$actionPath}' 方法权限缺失，请联系管理员！");
            //}
            else{
                $this->assign("status", $status);
                $this->assign("errorMsg", $mid===false ? $actionPath." Not Found" : "MID=".$mid.", ACT=".$actionPath." Not Permission");
                $this->display("Teacher@Login:error");
            }
            $this->logs(0);
            exit;
        }
        $this->logs(1);
    }
    /*
     * 下拉方法
     * @name:表名
     * @as:  变量名
     */
    public function xiala($name,$as){
        $shuju=M($name);
        $one= $shuju->select();
        $this->assign($as,$one);
    }

    /**
     * 检测登陆是否完整
     */
    private function checkLogin($actionPath)
    {
        //session中没有S_USER_NAME或者没有S_LOGIN_TYPE
        //表示没有登陆或者session到期，返回到登陆页，重新登陆
        if(session("?S_USER_NAME")==false || session("?S_LOGIN_TYPE")==false)
        {
            $status = 707;
            if($this->_hasJson)
            {
                header("HTTP/1.0 ".$status." Not Login");
            }
            else if($actionPath=="/Teacher/Index/index")
            {
                redirect("/Teacher/Login/index");
            }
            else
            {
                $this->assign("status", $status);
                $this->display("Teacher@Login:error");
            }
            $this->logs(0);
            exit;
        }

        //如果要LOGINS表中找不到相应该用户名(USERNAME)、SESSIONID和IP地址
        //表示用户登陆被重置，需要重新登陆
        $data = $this->model->sqlFind("select count(*) as NCOUNT from Sessions where SessionID=? and RemoteIP=?",
            array(session("S_GUID"), get_client_ip()));
        if($data["NCOUNT"]==0){
            $status = 708;
            if($this->_hasJson){
                header("HTTP/1.0 ".$status." Login Expired Or Reset");
            }else{
                $this->assign("status", $status);
                $this->display("Teacher@Login:error");
            }
            $this->logs(0);
            exit;
        }
    }
    /**
     * 写入系统日志
     * @param unknown $status
     */
    private function logs($status){
    	$method=$_SERVER["REQUEST_METHOD"];
    	$query=$method=="POST"?print_r($_POST,true):print_r($_GET,true);
    	
    	$sql="INSERT INTO LOGS(USERNAME,EMAIL,REMOTEHOST,REMOTEIP,DERIVEDFROM,USERAGENT,COOKIEUSER,".
    	"COOKIEROLES,COOKIEGROUP,SCRIPTNAME,PATHINFO,QUERY,METHOD,TITLE,REQUESTTIME,SUCCESS)".
    	"VALUES('".$_SESSION['S_USER_NAME']."','','".$_SERVER['HTTP_HOST']."','".get_client_ip().
    	"','','".substr($_SERVER['HTTP_USER_AGENT'],0,40)."','".$_SESSION['S_USER_NAME']."',".
    	"'".$_SESSION['S_ROLES']."','','".substr(getActionPath(),0,strrpos(getActionPath(),'/')).
    	"','".strrchr(getActionPath(),'/').
    	"','".trim($query)."','".$method."','','".date('Y-m-d H:i:s')."','$status')";
    	
    	$this->model->sqlExecute($sql);
    }

}
