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
use think\Request;

class Login
{
    public function checklogin($username='',$pwd='')
    {
        try {
            $Obj=new User();
            $result=$Obj->login($username,$pwd);
            $status=$result?1:0;
            $info=$result?'登录成功！':'用户名或者密码错误！';
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
}