<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:18
 */
namespace app\score\controller;

use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\ViewFinalScoreCourse;
use app\common\vendor\PHPExcel;

class Printer extends MyController
{

    public function finalcourselist($page = 1, $rows = 20, $year = '', $term = '', $courseno = '%', $coursename = '%', $school = '')
    {
        $result = null;
        try {
            $course = new ViewFinalScoreCourse();
            $result = $course->getList($page, $rows, $year, $term, $courseno, $coursename, $school);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    public function studentquery($page=1,$rows=20,$studentno='%',$classno='%')
    {
        $result=null;
        try {
            $student=new \app\common\service\Student();
            $result = $student->getList($page,$rows,$studentno,'%',$classno,'','','');
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

    public function studentexport($studentno='%',$classno='%',$graduate=1)
    {
        $result=null;
        try {
            PHPExcel::printScore("成绩单",$studentno,$classno,$graduate);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

}