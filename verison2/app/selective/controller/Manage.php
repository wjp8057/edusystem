<?php
// +----------------------------------------------------------------------
// |  [ MAKE YOUR WORK EASIER]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2016 http://www.nbcc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: fangrenfu <fangrenfu@126.com> 2016/6/19 16:42
// +----------------------------------------------------------------------

namespace app\selective\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\SchedulePlan;

class Manage extends MyController {
    public function  courselist($page = 1, $rows = 20, $year, $term, $courseno = '%', $coursename = '%', $classno = '%', $school = '',$amount='')
    {
        $result = null;
        try {
            $obj = new SchedulePlan();
            $condition = null;
            switch ($amount) {
                case 'A':
                    $condition['scheduleplan.attendents']=array('exp','<20');
                    break;
                case 'B':
                    $condition['scheduleplan.attendents']=array('exp','>scheduleplan.estimate');
                    break;
                case 'C':
                    $condition['scheduleplan.attendents']=array('exp','<scheduleplan.estimate');
                    break;
                default:
                    break;
            }
            $result = $obj->getList($page, $rows, $year, $term, $courseno, $coursename, $classno, $school, $condition);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

} 