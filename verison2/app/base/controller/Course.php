<?php
// +----------------------------------------------------------------------
// |  [ MAKE YOUR WORK EASIER]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2016 http://www.nbcc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: fangrenfu <fangrenfu@126.com> 2016/6/17 21:31
// +----------------------------------------------------------------------

namespace app\base\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\CourseForm;
use app\common\service\CourseType;
use app\common\service\Program;
use app\common\vendor\PHPExcel;

class Course extends MyController {
    public function type($page = 1, $rows = 20)
    {
        $result=null;
        try {
            $type = new CourseType();
            $result = $type->getList($page, $rows);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    public function updatetype(){
        $result=null;
        try {
            $type = new CourseType();
            $result = $type->update($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function form($page = 1, $rows = 20)
    {
        $result=null;
        try {
            $form = new CourseForm();
            $result = $form->getList($page, $rows);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    public function updateform(){
        $result=null;
        try {
            $form = new CourseForm();
            $result = $form->update($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    public function courselist($page = 1, $rows = 20,$courseno='%',$coursename='%',$school='')
    {
        $result=null;
        try {
            $course = new \app\common\service\Course();
            $result = $course->getList($page,$rows,$courseno,$coursename,$school);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function updatecourse(){
        $result=null;
        try {
            $course = new \app\common\service\Course();
            $result = $course->update($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function export($courseno, $coursename, $school)
    {
        try {
            $course = new \app\common\service\Course();
            $result = $course->getList(1, 10000,$courseno,$coursename,$school);
            $data = $result['rows'];
            $file = "课程列表";
            $sheet = '全部';
            $title = '';
            $template = array("courseno" => "课号", "coursename" => "课名","schoolname"=>"开课学院", "credits" => "学分", "total" => "总学时","week"=>"周数",
                "hours"=>"每周总学时", "lhours"=>"每周理论","experiments"=>"每周实验","computing"=>"每周上机","khours"=>"每周讨论","shours"=>"每周实训",
                "zhours"=>"每周自主", "typename" => "类型","formname"=>"形式","quarter"=>"开课学期","rem"=>"备注");
            $string = array("courseno");
            $array[] = array("sheet" => $sheet, "title" => $title, "template" => $template, "data" => $data, "string" => $string);
            PHPExcel::export2Excel($file, $array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    public function equalcourselist($page=1,$rows=20,$courseno='%',$equalcourseno='%',$programno='%',$school=''){
        $result=null;
        try{
            $program=new Program();
            $result =$program->equalCourseList($page,$rows,$courseno,$equalcourseno,$programno,$school);

        }catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    public function equalcourseupdate(){
        $result=null;
        try{
            $program=new Program();
            $result=$program->equalCourseUpdate($_POST);
        }catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function eqexport($courseno='%',$equalcourseno='%',$programno='%',$school='')
    {
        try {
            $program=new Program();
            $result =$program->equalCourseList(1,10000,$courseno,$equalcourseno,$programno,$school);
            $data = $result['rows'];
            $file = "等价课程列表";
            $sheet = '全部';
            $title = '';
            $template = array("programno" => "教学计划号", "progname" => "计划名称","progschoolname"=>"所属学院", "courseno" => "原课号", "coursename" => "原课名",
                "credits"=>"原学分","schoolname"=>"原课程学院","eqcourseno"=>"等价课号", "eqcoursename"=>"等价课名","eqcredits"=>"等价学分","eqschoolname"=>"等价学院");
            $string = array("programno,courseno,eqcourseno");
            $array[] = array("sheet" => $sheet, "title" => $title, "template" => $template, "data" => $data, "string" => $string);
            PHPExcel::export2Excel($file, $array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

}