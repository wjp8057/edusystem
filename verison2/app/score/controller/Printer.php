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
use app\common\service\Score;
use app\common\service\Sport;
use app\common\service\Student;
use app\common\service\ViewFinalScoreCourse;
use app\common\vendor\PHPExcel;

class Printer extends MyController
{

    public function finalcourselist($page = 1, $rows = 20, $year = '', $term = '', $courseno = '%', $coursename = '%', $school = '',$type='')
    {
        $result = null;
        try {
            $course = new ViewFinalScoreCourse();
            $result = $course->getList($page, $rows, $year, $term, $courseno, $coursename, $school,$type);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    public function studentquery($page=1,$rows=20,$studentno='%',$classno='%')
    {
        $result=null;
        try {
            $student=new Student();
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

    public function finalunlock($year,$term,$courseno)
    {
        $result = null;
        try {
            $score = new Score();
            $result = $score->unlockCourse($year,$term,$courseno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function sport($page = 1, $rows = 20, $studentno = '%', $year = '', $classno= '%', $school = '',$score='')
    {
        $result = null;
        try {
            $obj = new Sport();
            $result = $obj->getList($page, $rows,$studentno, $year, $classno, $school,$score);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function exportsport($studentno = '%', $year = '', $classno= '%', $school = '',$score=''){

        $result=null;
        try {
            $obj = new Sport();
            $result = $obj->getList(1, 10000,$studentno, $year, $classno, $school,$score);
            $file="体质健康测试成绩";
            $data=$result['rows'];
            $sheet='全部';
            $title=$file;
            $template= array("year"=>"学年","studentno"=>"学号","studentname"=>"姓名","classname"=>"班级","schoolname"=>"学院",
                "grade"=>"等级", "score"=>"成绩");
            $string=array("studentno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
}