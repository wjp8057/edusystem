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
// | Created:2016/11/29 12:03
// +----------------------------------------------------------------------

namespace app\exam\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\TestPlan;

class Teacher extends MyController {
    //考试课程列表
    public function  courselist($page=1,$rows=20,$year,$term,$type,$flag='',$school='',$studentschool='')
    {
        $result=null;
        try {
            $obj = new TestPlan();
            $result = $obj->getList($page,$rows,$year,$term,$type,$flag,$school,$studentschool);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //检索教师
    public function query($page = 1, $rows = 20, $teacherno = '%', $name = '%', $school = '')
    {
        $result = null;
        try {
            $teacher = new \app\common\service\Teacher();
            $result = $teacher->getList($page, $rows, $teacherno, $name, $school);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //设置监考
    public function courseupdate(){
        $result=null;
      //  try {
            $obj = new TestPlan();
            $result = $obj->updateTeacher($_POST);
     /*   } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }*/
        return json($result);
    }
}