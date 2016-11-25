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
// | Created:2016/11/24 14:38
// +----------------------------------------------------------------------

namespace app\quality\controller;

//学生评教
use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\QualityStudent;
use app\common\service\QualityStudentDetail;

class Student extends MyController {
    //同步课程
    public function syncourse($year,$term,$courseno='%'){
        $result = null;
        try {
            $obj = new QualityStudent();
            $result = $obj->synCourse($year,$term,$courseno);

        } catch (\Exception $e) {
           MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //锁定/开锁
    public function lockfree($year,$term,$courseno='%',$lock=1){
        $result = null;
        try {
            $obj = new QualityStudent();
            $result = $obj->setCourseStatus($year,$term,$courseno,$lock);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //获取课程列表
    public function courselist($page=1,$rows=20,$year,$term,$courseno='%',$coursename='%',$teachername='%',$school='',$type='',$enabled='',$lock='')
    {
        $result=null;
        try {
            $obj=new QualityStudent();
            $result =  $obj->getList($page,$rows,$year,$term,$courseno,$coursename,$teachername,$school,$type,$enabled,$lock);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

    //更新课程信息
    public function courseupdate(){
        $result=null;
        try {
            $obj=new QualityStudent();
            $result =  $obj->update($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //检索学生
    public function studentsearch($page=1,$rows=50,$studentno='%',$name='%',$classno='%',$school=''){
        $result=null;
        try {
            $student=new \app\common\service\Student();
            $result = $student->getList($page,$rows,$studentno,$name,$classno,$school);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    public function studentlist($page=1,$rows=20,$id)
    {
        $result=null;
        try {
            $obj=new QualityStudentDetail();
            $result =  $obj->getList($page,$rows,$id);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
}