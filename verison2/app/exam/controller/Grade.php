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
// | Created:2016/12/15 14:02
// +----------------------------------------------------------------------

namespace app\exam\controller;


use app\common\access\Item;
use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\ExamApply;
use app\common\service\ExamNotification;
use app\common\service\StandardExams;
use app\common\vendor\PHPExcel;

class Grade extends MyController {

    public function  query($page = 1, $rows = 20,$examname='%')
    {
        $result=null;
        try {
            $obj=new StandardExams();
            $result=$obj->getList($page,$rows,$examname);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function update()
    {
        $result=null;
        try {
            $obj=new StandardExams();
            $result=$obj->update($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //更新考试通告
    public function noteupdate()
    {
        $result=null;
        try {
            $obj=new ExamNotification();
            $result=$obj->update($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function  notequery($page = 1, $rows = 20,$year,$term,$lock='')
    {
        $result=null;
        try {
            $obj=new ExamNotification();
            $result=$obj->getList($page,$rows,$year,$term,$lock);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //报名情况查询
    public function applyquery($page = 1, $rows = 30,$map,$studentno='%',$studentname='%',$classno='%',$school='',$fee=''){
        $result=null;
        try {
            $obj=new ExamApply();
            $result=$obj->getList($page,$rows,$map,$studentno,$studentname,$classno,$school,$fee);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    public function applydetailquery($page = 1, $rows = 30,$map,$studentno='%',$studentname='%',$classno='%',$school='',$fee=''){
        $result=null;
        try {
            $obj=new ExamApply();
            $result=$obj->getListDetail($page,$rows,$map,$studentno,$studentname,$classno,$school,$fee);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //检索学生信息，获得各类等级考试成绩
    public function studentsearch($page=1,$rows=20,$studentno='%',$studentname='%',$classno='%',$school=''){
        $result=null;
        try {
            $obj=new ExamApply();
            $result=$obj->searchStudent($page,$rows,$studentno,$studentname,$classno,$school);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //更新学生报名单
    public function enroll()
    {
        $result=null;
        try {
            $obj=new  ExamApply();
            $result=$obj->update($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //导出学生名单
    public function exportexcel($map,$studentno='%',$studentname='%',$classno='%',$school='',$fee='')
    {
        try {
            $exam = Item::getExamNotificationItem($map);
            $obj = new ExamApply();
            $result = $obj->getListDetail(1, 5000, $map, $studentno, $studentname, $classno, $school, $fee);
            $data = $result['rows'];
            $amount = count($data);
            $file = $exam['year'] . "学年第" . $exam['term'] . "学期" . $exam['examname'] . "考试报名情况表";
            $sheet = '全部';
            $title = $file;
            $subtitle = "学校代码：" . $exam['schoolcode'] . "  级别代码：" . $exam['testcode'] . "  报名费：" . $exam['fee'] . "  人数：" . $amount . "  费用总计：" . $amount * $exam['fee'];
            $template = array('studentno' => '学号', 'studentname' => '姓名', 'sexname' => '性别', 'nationalityname' => '民族', 'birthday' => '出生日期', 'id' => '身份证号',
                "schoolname" => "学院", "classname" => "班级", "grade" => "年级", "years" => "学制","fee"=>"缴费",
                "pretcob" => "英语B级", "pretcoa" => "英语A级", "cet3" => "英语三级", "cet4" => "英语四级");
            $string = array("studentno", "birthday", "id");
            $array[] = array("sheet" => $sheet, "title" => $title, "template" => $template, "data" => $data, "string" => $string, 'subtitle' => $subtitle);
            PHPExcel::export2Excel($file, $array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
}