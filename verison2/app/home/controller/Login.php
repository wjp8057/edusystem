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
use app\common\vendor\CAS;
use think\Request;

class Login
{
    public function checklogin($username='',$pwd='')
    {
        try {
            $Obj=new User();
            $result=$Obj->login($username,$pwd);
            $status=$result;
            $info=$result==0?'用户名或者密码错误！':'登录成功！';
            return json(['info'=>$info,'status'=>$status]);
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

    /**
     *统一登录
     */
    public function caslogin($ticket=""){
        $status=CAS::signinNormal($ticket);
        echo $status;
        switch($status){
            case 1:
                header('Location:'.Request::instance()->root().'/home/index/index');
                break;
            case 2:
                header('Location:'.Request::instance()->root().'/teacher/index/index');
                break;
            case 3:
                header('Location:/Student/Index/index');
                break;
            default:
                echo "登录失败";
                break;
        }
    }
}