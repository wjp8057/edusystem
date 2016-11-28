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
use app\common\service\TestCourse;
use app\common\vendor\PHPExcel;

class End extends MyController{
    //获取统一排考课程列表
    public function courselist($page = 1, $rows = 20, $year, $term,$courseno='%',$coursename='%',$classno='%',$school='',$exam='')
    {
        $result = null;
        try {
            $obj = new SchedulePlan();
            $condition = null;
            if($exam!='')
                $condition['scheduleplan.exam']=$exam;
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
    public function  export ($year, $term,$courseno='%',$coursename='%',$classno='%',$school='',$exam=''){
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
            for($i=0;$i<$count;$i++)
                $data[$i]['exam']=$data[$i]['exam']=="1"?"是":"否";
            $template= array("courseno"=>"课号","coursename"=>"课名","schoolname"=>"开课学院","examtypename"=>"考试类型","classname"=>"主修班级",
                "exam"=>"统一排考");
            $string=array("courseno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }

}