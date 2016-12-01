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
namespace app\base\controller;

use app\common\access\Item;
use app\common\access\MyAccess;
use app\common\access\Template;
use app\common\service\Action;
use app\common\service\Classes;
use app\common\service\Classroom;
use app\common\service\RoomReserve;
use app\common\service\Schedule;
use app\common\service\Student;

class Index extends Template
{
    public function index()
    {
        $Obj = new Action();
        $menuJson = array('menus' => $Obj->getUserAccessMenu(session('S_USER_NAME'), 240));
        $this->assign('menu', json_encode($menuJson));
        return $this->fetch();
    }

    public function studentdetail($studentno = '', $op = '')
    {
        $student = null;
        if ($studentno != '' && $op != 'add') {
            MyAccess::checkStudentSchool($studentno);
            $obj = new Student();
            $student = $obj->getStudentDetail($studentno);
        }
        $this->assign('student', $student);
        return $this->fetch();
    }

    function roomtimetable($year = '', $term = '', $who)
    {
        try {
            $year=$year==''?get_year_term()['year']:$year;
            $term=$term==''?get_year_term()['term']:$term;
            $title['year'] = $year;
            $title['term'] = $term;
            $title['time'] = date("Y-m-d H:i:s");
            $title['name'] = Item::getRoomItem($who)['name'];
            $this->assign('title', $title);
            $schedule = new Schedule();
            $time = $schedule->getRoomTimeTable($year, $term, $who);
            $this->assign('time', $time);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch('all@index/timetable');
    }

    function roomtimetableall($year = '', $term = '', $who)
    {
        try {
            $year=$year==''?get_year_term()['year']:$year;
            $term=$term==''?get_year_term()['term']:$term;
            $title['year'] = $year;
            $title['term'] = $term;
            $title['time'] = date("Y-m-d H:i:s");
            $title['name'] = Item::getRoomItem($who)['name'];
            $this->assign('title', $title);
            $schedule = new Schedule();
            $time = $schedule->getRoomTimeTable($year, $term, $who, true);
            $this->assign('time', $time);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch('all@index/timetableall');
    }

    function roomreservenote($recno)
    {
        try {

            $room = new RoomReserve();
            $reserve = $room->getReserveRoomInfo($recno);
            if($reserve['approved']==0) {
                echo '教室申请尚未通过审核,无法打印！';
                exit;
            }
            $reserve['printdate'] = date("Y-m-d");
            $reserve['weeks'] = implode(' ', str_split(week_dec2bin_reserve($reserve['weeks'], 18), 4));
            $this->assign('reserve', $reserve);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }

    function classstudent($classno)
    {
        try {
            $classname =Item::getClassItem($classno)['classname'];
            $this->assign('classname', $classname);
            $this->assign('classno', $classno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }
} 