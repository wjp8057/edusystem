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
}