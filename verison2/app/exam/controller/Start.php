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


use app\common\access\MyController;

class Start extends  MyController{
    //读取补考学生名单
    public function  query($page = 1, $rows = 20,$studentno='%',$courseno='%',$courseschool='',$studentschool='',$examrem='')
    {
        $result=null;
        try {

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
}