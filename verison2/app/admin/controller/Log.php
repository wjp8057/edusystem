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

namespace app\admin\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\access\MyLog;

class Log extends MyController {

    public function query($page = 1, $rows = 10,$start='',$end='', $username = '%', $url = '%')
    {
        try {
            $log=new MyLog();
            $result = $log->getList($page, $rows,$start,$end, $username, $url);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    public function delete(){
        try {
            $log=new MyLog();
            $result = $log->clear();
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
} 