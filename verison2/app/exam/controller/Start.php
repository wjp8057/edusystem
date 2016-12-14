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
// | Created:2016/12/9 11:21
// +----------------------------------------------------------------------

namespace app\exam\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\Makeup;

class Start extends  MyController{
    //读取补考学生名单
    public function  query($page = 1, $rows = 20,$year,$term,$courseno='%',$studentno='%',$courseschool='',$studentschool='',$examrem='')
    {
        $result=null;
       // try {
            $obj=new Makeup();
            $result=$obj->getList($page,$rows,$year,$term,$courseno,$studentno,$courseschool,$studentschool,$examrem);
    /*    } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }*/
        return json($result);
    }
}