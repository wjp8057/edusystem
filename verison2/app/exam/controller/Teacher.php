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
// | Created:2016/11/29 12:03
// +----------------------------------------------------------------------

namespace app\exam\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\TestCourse;
use app\common\service\TestPlan;
use app\common\vendor\PHPExcel;

class Teacher extends MyController {
    //考试课程列表
    public function  courselist($page=1,$rows=20,$year,$term,$type,$flag='',$school='',$studentschool='')
    {
        $result=null;
        try {
            $obj = new TestPlan();
            $result = $obj->getList($page,$rows,$year,$term,$type,$flag,$school,$studentschool);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //检索教师
    public function query($page = 1, $rows = 20, $teacherno = '%', $name = '%', $school = '')
    {
        $result = null;
        try {
            $teacher = new \app\common\service\Teacher();
            $result = $teacher->getList($page, $rows, $teacherno, $name, $school);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //设置监考
    public function courseupdate(){
        $result=null;
        try {
            $obj = new TestPlan();
            $result = $obj->updateTeacher($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //检索最终排考结果
    public function schedulequery($page=1,$rows=20,$year,$term,$type,$flag='',$school='',$studentschool='',$teachername='%'){
        $result = null;
        try {
            $obj = new TestPlan();
            $result = $obj->getList($page,$rows,$year,$term,$type,$flag,$school,$studentschool,$teachername);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);

    }
    //导出最终结果
    public function export($year,$term,$type,$school='',$studentschool='',$flag='',$teachername='%')
    {
        try{
            $typename=TestCourse::getTypeName($type);
            $obj = new TestPlan();
            $result = $obj->getList(1,10000,$year,$term,$type,$flag,$school,$studentschool,$teachername);
            $data = $result['rows'];
            $file = $year."学年第".$term."学期".$typename."时间安排表";
            $sheet = '全部';
            $title = $file;
            $template = array('courseno'=>'课号','coursename'=>'课名','schoolname'=>'学院','classes'=>'班级','attendents'=>'人数','testtime'=>'时间',"roomno1"=>"考场1",
                "teachername1"=>"监考1","teachername2"=>"监考2","teachername3"=>"监考3","roomno2"=>"考场2","teachername4"=>"监考1","teachername5"=>"监考2",
                "teachername6"=>"监考3","rem"=>"备注");
            $string = array("courseno");
            $array[] = array("sheet" => $sheet, "title" => $title, "template" => $template, "data" => $data, "string" => $string);
            PHPExcel::export2Excel($file, $array);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    public function updaterem(){
        $result=null;
        try {
            $obj = new TestPlan();
            $result = $obj->updateRem($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
}