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
use app\common\service\Valid;

class Student extends MyController {
    //设置学评教开放时间
    public function updatedate($start,$stop)
    {
        $result = null;
        try {
            $obj = new Valid();
            $result = $obj->update($start,$stop,'C');

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
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
    //同步学生
    public function synstudent($year,$term,$courseno='%'){
        $result = null;
        try {
            $obj = new QualityStudentDetail();
            $result = $obj->synStudent($year,$term,$courseno);

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
    public function studentsearch($page=1,$rows=50,$studentno='%',$name='%',$classno='%',$school='',$courseno='',$year='',$term=''){
        $result=null;
        try {
            if($courseno=='') {
                $student = new \app\common\service\Student();
                $result = $student->getList($page, $rows, $studentno, $name, $classno, $school);
            }
            else {
                $obj=new QualityStudentDetail();
                $result=$obj->getStudentList($page,$rows,0,$year,$term,$courseno);
                $data=$result['rows'];
                $amount=count($data);
                for($i=0;$i<$amount;$i++)
                    $data[$i]['name']=$data[$i]['studentname'];
                $result['rows']=$data;
            }
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
            $result =  $obj->getStudentList($page,$rows,$id);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

    public function studentupdate(){
        $result=null;
        try {
            $obj=new  QualityStudentDetail();
            $result =  $obj->update($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

}