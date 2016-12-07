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
use app\common\service\R32;
use app\common\service\SchedulePlan;
use app\common\service\Selective;
use app\common\service\Student;

class Manage extends MyController {
    //获取课程列表
    public function  courselist($page = 1, $rows = 20, $year, $term, $courseno = '%', $coursename = '%', $classno = '%', $school = '',$amount='')
    {
        $result = null;
        try {
            $obj = new SchedulePlan();
            $condition = null;
            switch ($amount) {
                case 'A':
                    $condition['scheduleplan.attendents']=array('exp','<30');
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
    //获取某课程选课学生列表
    public function  coursestudent( $year, $term, $courseno)
    {
        $result = null;
        try {
            $obj = new R32();
            $result = $obj->getStudentList(1,10000,$year,$term,$courseno);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //同步选课人数与selective表
    public function syncourse($year,$term){
        $result = null;
        try {
            $obj=new SchedulePlan();
            $obj->updateAttendent($year,$term);
            $obj = new Selective();
            $result = $obj->update($year,$term);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //同步选课人数与selective表
    public function estimate(){
        $result = null;
        try {
            $obj=new SchedulePlan();
            $result=$obj->updateEstimate($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function updatestudent(){
        $result = null;
        try {
            $obj=new R32();
            $result=$obj->update($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //检索学生
    public function studentquery($page=1,$rows=50,$studentno='%',$name='%',$classno='%',$school=''){
        $result=null;
        try {
            $student=new Student();
            $result = $student->getList($page,$rows,$studentno,$name,$classno,$school);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

    public function coursequery($page=1,$rows=50,$year,$term,$courseno){
        $result=null;
        try {
            $obj=new R32();
            $result = $obj->getStudentList($page,$rows,$year,$term,$courseno);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

} 