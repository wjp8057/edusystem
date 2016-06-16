<?php
// +----------------------------------------------------------------------
// |  [ MAKE YOUR WORK EASIER]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2016 http://www.nbcc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: fangrenfu <fangrenfu@126.com>
// +----------------------------------------------------------------------

namespace app\common\access;
use think\Request;

class Template extends \think\Controller
{
    public function _initialize(){
        try {
            //实现父层验证功能。
            //替换模板信息
            //用户基本信息
            $userinfo=null;
            $userinfo['username']=session('S_USER_NAME');//用户名登录名
            $userinfo['realname']=session('S_TEACHER_NAME');//真实姓名
            $userinfo['teacherno']=session('S_TEACHERNO');//真实姓名
            $userinfo['school']=session('S_USER_SCHOOL'); //所在学院代码
            $userinfo['schoolname']=session('S_USER_SCHOOL_NAME');//学院名称
            $userinfo['manage']=session('S_MANAGE');//是否主管部门1是/0否
            $this->assign('USERINFO',$userinfo);

            //当前学年学期
            $yearterm=get_year_term();
            $this->assign('YEARTERM',$yearterm);

            //下个学年学期
            $nextyearterm=get_next_year_term();
            $this->assign('NEXTYEARTERM',$nextyearterm);
            $request = Request::instance();
            $root=$request->root();
            $action=$root.'/'.$request->module().'/'.$request->controller().'/'.$request->action();
            $this->assign("ROOT", $root);
            $this->assign("ACTION",$action);

            $this->assign("TITLE",TITLE);
            $this->assign("COPYRIGHT",COPYRIGHT);
            MyAccess::checkAccess('R');
            $log=new MyLog();
            $log->write('R');
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    /**
     * @param $name
     * @return mixed
     */
    public function _empty($name)
    {
        return $this->fetch($name);
    }
}
