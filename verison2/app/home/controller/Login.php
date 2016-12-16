<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/4
 * Time: 8:00
 */
namespace app\home\controller;
use app\common\access\MyAccess;
use app\common\service\User;
use app\common\vendor\Captcha;
use app\common\vendor\CAS;
use app\common\vendor\MultiServer;
use think\Request;

class Login
{
    public function checklogin($username='',$pwd='',$code='')
    {
        try {
            if(session('S_LOGINTIMES')>3&&!Captcha::check($code))
                return json(['info'=>'验证码错误！','status'=>"-1"]);
            $result=MyAccess::login($username,$pwd);
            $status=$result;
            if($result==0)
            {
                $info="用户名或者密码错误！";
                $times=session('S_LOGINTIMES')+1;
                session('S_LOGINTIMES',$times);
            }
            else
            {
                session('S_LOGINTIMES',0);
                $info="登录成功";
            }
            return json(['info'=>$info,'status'=>$status,'times'=>session('S_LOGINTIMES')]);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    /**
     * 用户注销页面
     */
    public function logout()
    {
        session(null);
        header('Location:'.Request::instance()->root().'/home/index/login');
    }
    private  function switchbystatus($status)
    {
        switch ($status) {
            case 1:
                header('Location:' . Request::instance()->root() . '/home/index/index');
                break;
            case 2:
                header('Location:' . Request::instance()->root() . '/teacher/index/index');
                break;
            case 3:
                header('Location:' . Request::instance()->root() . '/student/index/index');
                break;
            default:
                echo  "登录失败";
                break;
        }
    }
    /**
     *统一登录
     */
    public function caslogin($ticket="",$forward=""){
        $service= 'http://'.$_SERVER["SERVER_NAME"]."/web/home/login/caslogin?forward=".$forward;
        $status=CAS::signinNormal($ticket,$service);
        if($forward=="score"){
            header('Location:' . Request::instance()->root() . '/teacher/?233');
        }
        else if($forward=="login") {
            self::switchbystatus($status);
        }
        else if ($forward=="room"){
            header('Location:' . Request::instance()->root() . '/teacher/?1478');
        }
    }

    public function redirect($serverip=''){
        if($serverip=='')
            $serverip=MultiServer::selectServer();
        MultiServer::changeServer($serverip,'/home/login/redirectlogin');
    }

    public function redirectlogin($username='',$timeflag=''){
        $result=MultiServer::checkFlag($username,$timeflag);
        if($result['status']){
            $status=MyAccess::signInAsUserName($username);
            if($result==0)
                $status=MyAccess::signInAsStudent($username);
            self::switchbystatus($status);
        }
        else
            echo $result['info'];

    }

}