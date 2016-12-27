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


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\ExamApply;
use app\common\service\ExamNotification;
use app\common\service\StandardExams;

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
    //更新报名状态
    public function applyupdate()
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
}