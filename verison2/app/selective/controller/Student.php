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
// | Created:2016/12/7 10:08
// +----------------------------------------------------------------------

namespace app\selective\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\R32;
use app\common\service\Selective;
use app\common\service\ViewScheduleTable;

class Student extends MyController {
        //    修改学生密码
    public function changepassword($studentno='',$password=''){
        $result=null;
        try {
            $student=new \app\common\service\Student();
            $result = $student->changePassword($studentno, $password);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

    //检索学生信息
    public function query($page=1,$rows=20,$year,$term,$studentno='%',$name='%',$classno='%',$school='')
    {
        $result=null;
        try {
            $obj=new Selective();
            $result =$obj->getList($page,$rows,$year,$term,$studentno,$name,$classno,$school);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //已选课程
    public function selected($page=1,$rows=20,$year,$term,$studentno){
        try {
            $obj=new R32();
            return $obj->getSelectedCourse($page,$rows,$year,$term,$studentno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    //更新课程
    public function update()
    {
        $result = null;
        try {
            $obj = new R32();
            $result = $obj->selectByStudent($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //检索课程
    public function querycourse($page=1,$rows=20,$year,$term,$courseno='%',$classno='%',$coursename='%',$teachername='%',$school='',$weekday='',$time='',$rest=''){
        try {
            $obj=new ViewScheduleTable();
            return $obj->getList($page,$rows,$year,$term,$courseno,$classno,$coursename,$teachername,$school,$weekday,$time,$rest);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
}