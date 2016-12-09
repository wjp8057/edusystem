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
// | Created:2016/12/9 8:42
// +----------------------------------------------------------------------

namespace app\selective\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\R32Dump;

class Recycle extends  MyController {
    public function query($page=1,$rows=20,$year,$term,$studentno='%',$courseno='%'){
        try {
            $obj=new R32Dump();
            return $obj->getList($page,$rows,$year,$term,$studentno,$courseno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    public function recover(){
        try {
            $obj=new R32Dump();
            return $obj->recover($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
}