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
namespace app\major\controller;
use app\common\access\Template;
use app\common\service\Action;
class Index extends Template{
    /**首页
     * @return mixed
     * @throws \think\Exception
     */
    public function index(){
        $obj=new Action();
        $menuJson=array('menus'=>$obj->getUserAccessMenu(session('S_USER_NAME'),1417));
        $this->assign('menu',json_encode($menuJson));
        return $this->fetch();
    }
}