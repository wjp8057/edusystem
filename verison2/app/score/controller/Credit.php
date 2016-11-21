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
// | Created:2016/11/21 14:42
// +----------------------------------------------------------------------

namespace app\score\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\Project;
use app\common\service\Valid;

class Credit extends MyController {
    public function updatedate($start,$stop)
    {
        $result = null;
        try {
            $obj = new Valid();
            $result = $obj->update($start,$stop,'A');

        } catch (\Exception $e) {
           MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    public function creativelist($page = 1, $rows = 20, $year, $term, $type='A', $school = '')
    {
        $result = null;
        try {
            $obj = new Project();
            $result = $obj->getList($page, $rows, $year, $term, $type, $school);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function creativeprojectupdate()
    {
        $result = null;
        try {
            $obj = new Project();
            $result = $obj->update($_POST);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
}