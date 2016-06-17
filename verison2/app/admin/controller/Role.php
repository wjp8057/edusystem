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

class Role extends MyController
{
//显示信息
    public function query($page = 1, $rows = 20)
    {
        $result = null;
        try {
            $role = new \app\common\service\Role();
            $result = $role->getRoleList($page, $rows);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //更新信息
    public function update()
    {
        $result = null;
        try {
            $role = new \app\common\service\Role();
            $result = $role->updateRole($_POST);//无法用I('post.')获取二维数组
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
} 