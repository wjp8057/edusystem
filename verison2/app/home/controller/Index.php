<?php
namespace app\home\controller;

use app\common\access\MyAccess;
use think\Controller;
use think\Request;

class Index extends Controller
{
    public function _initialize()
    {
        $request = Request::instance();
        $root=$request->root();
        $action=$root.'/'.$request->module().'/'.$request->controller().'/'.$request->action();
        $this->assign("ROOT", $root);
        $this->assign("ACTION",$action);

        $this->assign("TITLE",config('site.title'));
        $this->assign("COPYRIGHT",config('site.copyright'));
    }

    public function index()
    {
        try {
            $request = Request::instance();
            if(session('S_LOGIN_TYPE')==2) {
                header('Location:' . $request->root().'/student/');
                exit();
            }
            MyAccess::getAccess();
            config('auth')&& MyAccess::checkAccess('R');
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }

    public function login()
    {
        $guid = getGUID(session_id());
        session('S_GUID', $guid);
        $this->assign('GUID', $guid);
        $this->assign('order',rand(1,6));
        return $this->fetch();
    }
    public function  _empty(){
        return $this->fetch();
    }
}
