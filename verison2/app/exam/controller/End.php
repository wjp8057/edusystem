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
// | Created:2016/11/14 8:58
// +----------------------------------------------------------------------

namespace app\exam\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\SchedulePlan;
use app\common\service\Score;
use app\common\service\TestCourse;
use app\common\vendor\PHPExcel;

class End extends MyController{
    //获取统一排考课程列表
    public function courselist($page = 1, $rows = 20, $year, $term,$courseno='%',$coursename='%',$classno='%',$school='',$exam='',$degree='')
    {
        $result = null;
        try {
            $obj = new SchedulePlan();
            $condition = null;
            if($exam!='')
                $condition['scheduleplan.exam']=$exam;
            if($degree!='')
                $condition['scheduleplan.degree']=$degree;
            $result = $obj->getList($page, $rows, $year, $term, $courseno, $coursename, $classno, $school, $condition);


        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //更新课程信息
    public function  courseupdate()
    {
        $result = null;
        try {
            $obj=new SchedulePlan();
            $result = $obj->updateExam($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //导出数据
    public function export ($year, $term,$courseno='%',$coursename='%',$classno='%',$school='',$exam=''){
        try{
            $student=new SchedulePlan();
            $condition = null;
            if($exam!='')
                $condition['scheduleplan.exam']=$exam;
            $result = $student->getList(1,5000,$year, $term, $courseno, $coursename, $classno, $school, $condition);
            $data=$result['rows'];
            $file=$year."学年第".$term."学期统考安排";
            $sheet='全部';
            $title='统考课程列表';
            $count=count($data);
            for($i=0;$i<$count;$i++) {
                $data[$i]['exam'] = $data[$i]['exam'] == "1" ? "是" : "否";
                $data[$i]['degree'] = $data[$i]['degree'] == "1" ? "是" : "否";
            }
            $template= array("courseno"=>"课号","coursename"=>"课名","schoolname"=>"开课学院","examtypename"=>"考试类型","classname"=>"主修班级",
                "exam"=>"统一排考",'degree'=>'学位课程');
            $string=array("courseno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    //缓考检索学生
    public function  delayquery($page = 1, $rows = 20,$year,$term,$courseno='%',$studentno='%',$courseschool='',$studentschool='',$delay='')
    {
        $result=null;
        try {
            $obj=new Score();
            $result=$obj->getStudentDetail($page,$rows,$year,$term,$courseno,$studentno,$courseschool,$studentschool,$delay);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //缓考状态保存
    public function  delayupdate()
    {
        $result=null;
        try {
            $obj=new Score();
            $result=$obj->updateDelay($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //导出缓考信息
    public function delayexport($year,$term,$courseno='%',$studentno='%',$courseschool='',$studentschool='',$delay=''){
        try{
            $obj=new Score();
            $result=$obj->getStudentDetail(1,2000,$year,$term,$courseno,$studentno,$courseschool,$studentschool,$delay);
            $data=$result['rows'];
            $file=$year."学年第".$term."学期学期期末缓考名单";
            $sheet='全部';
            $title=$file;
            $template= array("courseno"=>"课号","coursename"=>"课名","courseschoolname"=>"开课学院","studentno"=>"学号","studentname"=>"姓名",
                "classname"=>"班级","plantypename"=>"类型","studentschoolname"=>"所在学院","delayname"=>"缓考原因");
            $string=array("studentno","courseno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }

    }

}