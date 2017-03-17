<?php
// +----------------------------------------------------------------------
// |  [ MAKE YOUR WORK EASIER]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2016 http://www.nbcc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: fangrenfu <fangrenfu@126.com> 2016/6/19 9:57
// +----------------------------------------------------------------------

namespace app\all\controller;


use app\common\access\Item;
use app\common\access\MyAccess;

class Info
{
    //获取课名
    public function  getcourseinfo($courseno)
    {
        $result = null;
        $status = 1;
        $coursename= null;
        $credits=null;
        $hours=null;
        try {
            $result = Item::getCourseItem($courseno, false);
            if ($result != null) {
                $coursename= $result['coursename'];
                $credits=$result['credits'];
                $hours=$result['hours'];
                $status = 1;
            } else {
                $coursename = '该课程不存在!';
                $status = 0;
            }
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json(["status" => $status, 'coursename' => $coursename,'hours'=>$hours,'credits'=>$credits]);
    }
    //获取教室名称
    public function getroominfo($roomno)
    {
        $result = null;
        $status = 1;
        try {
            $result = Item::getRoomItem($roomno,false);
            if ($result != null) {
                $result = $result['name'];
                $status = 1;
            } else {
                $result = '该教室不存在!';
                $status = 0;
            }
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json(["status" => $status, 'roomname' => $result]);
    }
    //获取教学计划名称
    public function  getprograminfo($programno){
        $result = null;
        $status = 1;
        try {
            $result = Item::getProgramItem($programno);
            if ($result != null) {
                $result = $result['progname'];
                $status = 1;
            } else {
                $result = '该教学计划不存在!';
                $status = 0;
            }
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json(["status" => $status, 'progname' => $result]);
    }
    //获取班级信息
    public function  getclassinfo($classno)
    {
        $result = null;
        $status = 1;
        $classname=null;
        $schoolname=null;
        try {
            $result =Item::getClassItem($classno,false);
            if ($result != null) {
                $classname = $result['classname'];
                $schoolname= $result['schoolname'];
                $status = 1;
            } else {
                $classname= '该班级不存在!';
                $status = 0;
            }
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json(["status" => $status, 'classname' => $classname,'schoolname'=>$schoolname]);
    }
    //获取教师信息
    public function  getteacherinfo($teacherno)
    {
        $result = null;
        $status = 1;
        $teachername=null;
        $schoolname=null;
        try {
            $result =Item::getTeacherItem($teacherno,false);
            if ($result != null) {
                $teachername = $result['teachername'];
                $schoolname= $result['schoolname'];
                $status = 1;
            } else {
                $teachername= '该教师不存在!';
                $status = 0;
            }
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json(["status" => $status, 'teachername' => $teachername,'schoolname'=>$schoolname]);
    }
    //获取学生信息
    public function  getstudentinfo($studentno)
    {
        $result = null;
        $status = 1;
        $studentname= null;
        try {
            $result = Item::getStudentItem($studentno, false);
            if ($result != null) {
                $studentname= $result['name'];
                $status = 1;
            } else {
                $studentname = '该学生不存在!';
                $status = 0;
            }
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json(["status" => $status, 'studentname' => $studentname]);
    }
}
