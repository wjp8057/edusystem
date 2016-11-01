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

namespace app\major\controller;

use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\Majorcode;
use app\common\service\Majordirection;
use app\common\service\Studentplan;
use app\common\vendor\PHPExcel;

class Graduate extends MyController
{
      //读取
    public function query($page = 1, $rows = 20,$studentno='%',$name='%',$classno='%',$classname='%',$school='',$status='')
    {
        $result = null;
        try {
            $obj=new Studentplan();
            $result = $obj->getGraduate($page,$rows,$studentno,$name,$classno,$classname,$school,$status);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }


} 