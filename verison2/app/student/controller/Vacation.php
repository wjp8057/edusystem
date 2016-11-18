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
// | Created:2016/11/18 8:41
// +----------------------------------------------------------------------

namespace app\student\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\Testplan;

class Vacation extends MyController {
    //读取考试安排
    public function testplan($page=1,$rows=20,$year,$term){
        try {
            $obj=new Testplan();
            $studentno=session('S_USER_NAME');
            return $obj->studentCourseList($page,$rows,$year,$term,$studentno);

        } catch (\Exception $e) {
           MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
}