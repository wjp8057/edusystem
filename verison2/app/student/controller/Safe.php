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
// | Created:2016/11/17 15:47
// +----------------------------------------------------------------------

namespace app\student\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\Student;

class Safe extends MyController {
    public function update($oldpwd,$newpwd){
        try {
            $obj=new Student();
            return $obj->changeSelfPassword($oldpwd,$newpwd);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
}