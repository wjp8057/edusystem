<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:41
 */

namespace app\quality\controller;


use app\common\access\Item;
use app\common\access\Template;
use app\common\access\MyAccess;
use app\common\service\Action;
use app\common\service\Course;
use app\common\service\R32;
use app\common\service\Schedule;
use app\common\service\Score;
use app\common\service\ViewScheduleTable;

class Index extends Template
{
    public function index()
    {
        try {
            $obj = new Action();
            $menuJson = array('menus' => $obj->getUserAccessMenu(session('S_USER_NAME'), 1372));
            $this->assign('menu', json_encode($menuJson));
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }

    public function expertaddscore($teacherno)
    {
        try {
            $this->assign('teacher', Item::getTeacherItem($teacherno));
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }
    function studentset(){
        try {
            $valid=Item::getValidItem('C');
            $this->assign('valid', $valid);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }

    function studentdetail($id){
        try {
            $course=Item::getQualityStudentItem($id);
            $this->assign('course', $course);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }
}