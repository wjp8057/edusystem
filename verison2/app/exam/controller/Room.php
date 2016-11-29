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
// | Created:2016/11/28 16:03
// +----------------------------------------------------------------------

namespace app\exam\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\Classroom;
use app\common\service\TestPlan;
use think\log\driver\Test;

class Room extends MyController {
    //读取教室列表
    public function  query($page = 1, $rows = 20, $roomno = '%', $name = '%', $building = '%', $area = '', $equipment = '',
                              $school = '', $status = '', $reserved = '', $seatmin = 0, $seatmax = 1000, $testmin = 0, $testmax = 1000)
    {
        $result=null;
        try {
            $room = new Classroom();
            $result = $room->getList($page, $rows, $roomno, $name, $building, $area, $equipment, $school, $status, $reserved, $seatmin, $seatmax, $testmin, $testmax);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //考试课程列表
    public function  courselist($page=1,$rows=20,$year,$term,$type,$flag='',$school='')
    {
        $result=null;
     //   try {
            $obj = new TestPlan();
            $result = $obj->getList($page,$rows,$year,$term,$type,$flag,$school);

    //    } catch (\Exception $e) {
       //     MyAccess::throwException($e->getCode(), $e->getMessage());
     //   }
        return json($result);
    }
    //更新考试课程的考场
    public function courseupdate(){
        $result=null;
        try {
            $course = new TestPlan();
            $result = $course->updateRoom($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
}