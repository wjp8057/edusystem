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

namespace app\admin\controller;
use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\access\Template;

class School extends MyController {
    //显示信息
    public function query($page = 1, $rows = 20)
    {
        try {
            $school=new \app\common\service\School();
            $result = $school->getSchoolList($page, $rows);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }

    //更新信息
    public function update()
    {
        try {
            $school=new \app\common\service\School();
            $result = $school->updateSchool($_POST);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
} 