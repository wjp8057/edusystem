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

namespace app\common\service;


use app\common\access\MyAccess;
use app\common\access\MyException;
use app\common\access\MyService;

class OEW  extends MyService{
    public  function getOEWItem($oew){
        $condition['code']=$oew;
        $result=$this->query->table('oewoptions')->field('rtrim(name) name,timebit,timebit2')->where($condition)->select();
        if(!is_array($result)||count($result)!=1)
            throw new \think\Exception('oew'.$oew, MyException::PARAM_NOT_CORRECT);
        return $result[0];
    }


} 