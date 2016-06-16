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
        header("Location:/web/");
    }
}