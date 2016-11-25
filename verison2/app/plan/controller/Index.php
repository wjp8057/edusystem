<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:41
 */

namespace app\plan\controller;


use app\common\access\Item;
use app\common\access\MyException;
use app\common\access\Template;
use app\common\access\MyAccess;
use app\common\service\Action;
use app\common\service\Schedule;
use think\Exception;

class Index extends Template
{
    /*
   * 教师个人信息页面首页
   */
    public function index()
    {
        try {
            $obj = new Action();
            $menuJson = array('menus' => $obj->getUserAccessMenu(session('S_USER_NAME'), 1393));
            $this->assign('menu', json_encode($menuJson));
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }
    //课表
    //班级课表
    function timetable($year, $term,$type,$who)
    {
        try {
            $title['year'] = $year;
            $title['term'] = $term;
            $title['time'] = date("Y-m-d H:i:s");
            switch($type){
                case 'C':
                    $title['name'] =Item::getClassItem($who)['classname'];
                    $this->assign('title', $title);
                    $schedule = new Schedule();
                    $time = $schedule->getClassTimeTable($year, $term, $who);
                    $this->assign('time', $time);
                    break;
                case 'R':
                    $title['name'] =Item::getRoomItem($who)['name'];
                    $this->assign('title', $title);
                    $schedule = new Schedule();
                    $time = $schedule->getRoomTimeTable($year, $term, $who);
                    $this->assign('time', $time);
                    break;
                case 'T':
                    $title['name'] =Item::getTeacherItem($who)['teachername'];
                    $this->assign('title', $title);
                    $schedule = new Schedule();
                    $time = $schedule->getTeacherTimeTable($year, $term, $who);
                    $this->assign('time', $time);
                    break;
                default:
                    throw new Exception('type undefined!', MyException::PARAM_NOT_CORRECT);
                    break;
            }

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch('timetable');
    }
}