<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:41
 */

namespace app\exam\controller;


use app\common\access\Template;
use app\common\access\MyAccess;
use app\common\service\Action;

class Index extends Template
{
    /*
   * 教师个人信息页面首页
   */
    public function index()
    {
        try {
            $obj = new Action();
            $menuJson = array('menus' => $obj->getUserAccessMenu(session('S_USER_NAME'), 1479));
            $this->assign('menu', json_encode($menuJson));
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }

}