<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:41
 */

namespace app\exam\controller;


use app\common\access\Item;
use app\common\access\Template;
use app\common\access\MyAccess;
use app\common\service\Action;
use app\common\service\Schedule;
use app\common\service\TestCourse;

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
    //场次时间设置
    public function flagtime($year,$term,$type)
    {
        $this->assign('type',$type);
        $this->assign('year',$year);
        $this->assign('term',$term);
        return $this->fetch();
    }
    //期末自动排考
    public function finalauto(){
        $type=['type'=>'A','typename'=>TestCourse::getTypeName('A')];
        $this->assign('type',$type);
        return $this->fetch('auto');
    }
    //期末考场安排
    public function finalroom(){
        $type=['type'=>'A','typename'=>TestCourse::getTypeName('A')];
        $this->assign('type',$type);
        return $this->fetch('room');
    }

    function roomtimetable($year = '', $term = '', $roomno = '')
    {
        try {
            $title['year'] = $year;
            $title['term'] = $term;
            $title['time'] = date("Y-m-d H:i:s");
            $title['roomname']=Item::getRoomItem($roomno)['name'];
            $this->assign('title', $title);
            $schedule = new Schedule();
            $time = $schedule->getRoomTimeTable($year, $term, $roomno);
            $this->assign('time', $time);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();

    }
}