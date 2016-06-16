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

        $this->assign("TITLE", TITLE);
        $this->assign("COPYRIGHT", COPYRIGHT);
    }

    public function index()
    {
        try {
            MyAccess::checkAccess('R');
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
        return $this->fetch();
    }

    public function  changepwd(){
        return $this->fetch();
    }
}
