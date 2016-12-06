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
// | Created:2016/12/6 8:54
// +----------------------------------------------------------------------

namespace app\teacher\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\TestPlan;

class Exam extends MyController {
    public function query($page = 1, $rows = 20,$year,$term,$type='A')
    {
        $result = null;
        try {
            $obj = new TestPlan();
            $teacherno = session('S_TEACHERNO');
            $result = $obj->getList($page, $rows,$year,$term,$type,'','','','%',$teacherno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
}