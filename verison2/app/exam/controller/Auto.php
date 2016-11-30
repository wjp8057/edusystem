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
// | Created:2016/11/28 14:06
// +----------------------------------------------------------------------

namespace app\exam\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\TestBatch;
use app\common\service\TestCourse;
use app\common\vendor\PHPExcel;

//考试设置
class Auto extends  MyController {
    //检索排考的课程
    public function query($page = 1, $rows = 20, $year, $term,$classno='%',$courseno='%',$status='',$type)
    {
        $result = null;
        try {
            $obj = new TestCourse();
            $result = $obj->getList($page, $rows, $year, $term,$courseno,$classno,$status,$type);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //载入课程
    public function load($year, $term,$type)
    {
        $result = null;
        try {
            $obj = new TestCourse();
            $result = $obj->loadCourse($year,$term,$type);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //批量开放与锁定课程
    public function lockfree($year,$term,$type,$courseno='%',$classno='%',$lock=0){
        $result = null;
        try {
            $obj = new TestCourse();
            $result = $obj->setCourseStatus($year,$term,$type,$courseno,$classno,$lock);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //更新课程信息
    public function  update()
    {
        $result = null;
        try {
            $obj=new TestCourse();
            $result = $obj->update($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //初始化排考
    public function  init($year,$term,$type)
    {
        $result = null;
        try {
            $obj=new TestCourse();
            $result = $obj->init($year,$term,$type);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //排考
    public function schedule($year,$term,$type,$courseno,$amount,$init=0,$start=0,$end=0)
    {
        $result = null;
        try {
            $obj=new TestCourse();
            $result = $obj->schedule($year,$term,$type,$courseno,$amount,$init,$start,$end);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //导出到excel表
    public function exportexcel($year,$term,$type)
    {
        try{
            $typename=TestCourse::getTypeName($type);
            $obj = new TestCourse();
            $result = $obj->getList(1,10000,$year,$term,'%','%','',$type);
            $data = $result['rows'];
            $file = $year."学年第".$term."学期".$typename."时间安排表";
            $sheet = '全部';
            $title = $file;
            $template = array('courseno'=>'课号','coursename'=>'课名','schoolname'=>'学院','classname'=>'班级','amount'=>'人数','flag'=>'时间',);
            $string = array("courseno");
            $array[] = array("sheet" => $sheet, "title" => $title, "template" => $template, "data" => $data, "string" => $string);
            PHPExcel::export2Excel($file, $array);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    //导出到排考表
    public function exportplan($year,$term,$type)
    {
        $result=null;
        try{
            $obj = new TestCourse();
            $result = $obj->exportPlan($year,$term,$type);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //读取考场时间设置情况
    public function  flagquery($page=1,$rows=20,$year,$term,$type)
    {
        $result = null;
        try {
            $obj=new TestBatch();
            $result = $obj->getList($page,$rows,$year,$term,$type);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //更新考场设置情况
    public function  flagupdate()
    {
        $result = null;
        try {
            $obj=new TestBatch();
            $result = $obj->update($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
}