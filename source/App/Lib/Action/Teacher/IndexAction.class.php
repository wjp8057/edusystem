<?php
/**
 * 教师登陆后首页信息
 * User: educk
 * Date: 13-11-21
 * Time: 下午12:57
 */
class IndexAction extends Action
{
    //此action不进行权限验证，只验证有没有以老师方式登陆过
    public function index()
    {
        //session中没有S_USER_NAME或者没有S_LOGIN_TYPE
        //表示没有登陆或者session到期，返回到登陆页，重新登陆
        if(session("?S_USER_NAME")==false || session("?S_LOGIN_TYPE")==false)
        {
            redirect("Teacher/Login/index");
            exit;

        //如果是学生则返回到学生登陆页去
        }elseif(session("S_LOGIN_TYPE")==2){
            redirect("Student/Index/index");
            exit;
        }
        $this->display();
    }
}