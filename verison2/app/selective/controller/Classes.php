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
// | Created:2016/12/7 10:07
// +----------------------------------------------------------------------

namespace app\selective\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\R32;
use app\common\service\R32Dump;

class Classes extends MyController {
      //检索班级信息
    public function query($page=1,$rows=20,$classno='%',$classname='%',$school='')
    {
        $result=null;
        try {
            $class=new \app\common\service\Classes();
            $result =  $class->getList($page,$rows,$classno,$classname,$school);

        } catch (\Exception $e) {
             MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //统一选必修课模块课
    public function selectall($year,$term,$classno,$type)
    {
        $result=null;
        try {
            if(!MyAccess::checkClassSchool($classno))
                $result = R32::selectAll($year,$term,$classno,$type);
            else
                $result=array('info'=>"您无法给其他学院的班级(".$classno.")统一选课",'status'=>"0");
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

}