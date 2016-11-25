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
// | Created:2016/11/25 12:19
// +----------------------------------------------------------------------

namespace app\plan\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\Conflict;

class Schedule extends MyController {
    public function conflictquery($page = 1, $rows = 20,$year,$term)
    {
        $result = null;
        try {
            $obj = new Conflict();
            $result = $obj->getList($page, $rows, $year, $term);

        } catch (\Exception $e) {
           MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function conflictinit($year,$term)
    {
        $result = null;
        try {
            $obj = new Conflict();
            $result = $obj->init($year,$term);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
}