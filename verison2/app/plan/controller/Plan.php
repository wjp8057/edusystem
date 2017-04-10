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
// | Created:2017/3/30 15:06
// +----------------------------------------------------------------------

namespace app\plan\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\CoursePlan;

class Plan extends MyController {
    public function init($year,$term,$classno,$start)
    {
        $result = null;
        try {
            $obj = new CoursePlan();
            $result = $obj->init($year,$term,$classno,$start);

        } catch (\Exception $e) {
           MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

}