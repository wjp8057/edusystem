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
// | Created:2016/6/15 9:54
// +----------------------------------------------------------------------

namespace app\selective\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\SchedulePlan;
use app\common\service\Student;
use app\common\service\Valid;
use app\common\service\ViewScheduleTable;

class Setting extends MyController
{
    public function  courselist($page = 1, $rows = 20, $year, $term, $courseno = '%', $coursename = '%', $classno = '%', $school = '', $halflock = '', $lock = '')
    {
        $result = null;
        try {
            $obj = new SchedulePlan();
            $condition = null;
            if ($halflock != '') $condition['scheduleplan.halflock'] = $halflock;
            if ($lock != '') $condition['scheduleplan.lock'] = $lock;
            $result = $obj->getList($page, $rows, $year, $term, $courseno, $coursename, $classno, $school, $condition);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function courseupdate()
    {
        $result = null;
        try {
            $obj = new SchedulePlan();
            $result = $obj->updateStatus($_POST);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function updateallstatus($year, $term, $halflock, $lock)
    {
        $result = null;
        try {
            $obj = new SchedulePlan();
            $result = $obj->updateAllStatus($year, $term, $halflock, $lock);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function studentlist($page = 1, $rows = 20, $studentno = '%', $name = '%', $school = '', $free = '')
    {
        $result = null;
        try {
            $obj = new Student();
            $result = $obj->getList($page, $rows, $studentno, $name, '%', $school, '', $free);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function studentupdate()
    {
        $result = null;
        try {
            $obj = new Student();
            $result = $obj->updateStatus($_POST);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function synscheduletable($year, $term)
    {
        $result = null;
        try {
            $obj = new ViewScheduleTable();
            $result = $obj->update($year, $term);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function updatedate($start,$stop)
    {
        $result = null;
        try {
            $obj = new Valid();
            $result = $obj->update($start,$stop,'B');

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
}