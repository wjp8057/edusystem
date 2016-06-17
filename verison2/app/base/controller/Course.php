<?php
// +----------------------------------------------------------------------
// |  [ MAKE YOUR WORK EASIER]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2016 http://www.nbcc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: fangrenfu <fangrenfu@126.com> 2016/6/17 21:31
// +----------------------------------------------------------------------

namespace app\base\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\CourseForm;
use app\common\service\CourseType;

class Course extends MyController {
    public function type($page = 1, $rows = 20)
    {
        $result=null;
        try {
            $type = new CourseType();
            $result = $type->getList($page, $rows);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    public function updatetype(){
        $result=null;
        try {
            $type = new CourseType();
            $result = $type->update($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function form($page = 1, $rows = 20)
    {
        $result=null;
        try {
            $form = new CourseForm();
            $result = $form->getList($page, $rows);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    public function updateform(){
        $result=null;
        try {
            $form = new CourseForm();
            $result = $form->update($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
} 