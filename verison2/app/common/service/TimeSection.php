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

class TimeSection extends MyService {
    public function getTimeSectionItem($time)
    {
        $condition['name']=$time;
        $result=$this->query->table('timesections')->field('rtrim(value) value,timebits,timebits2')->where($condition)->select();
        if(!is_array($result)||count($result)!=1)
            throw new \think\Exception('time'.$time, MyException::PARAM_NOT_CORRECT);
        return $result[0];
    }
} 