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

class Action extends MyController
{
    //显示信息
    public function query($action = '%', $description = '%', $searchid = '', $id = 1)
    {
        $result = null;
        try {
            $obj = new \app\common\service\Action();
            $result = $obj->getActionList($action, $description, $searchid, $id);

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
            $obj = new \app\common\service\Action();
            $result = $obj->updateAction($_POST);//无法用I('post.')获取二维数组

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //显示角色信息
    public function role($page = 1, $rows = 10, $id = 0)
    {
        $result = null;
        try {
            $obj = new \app\common\service\Action();
            $result = $obj->getActionRole($page, $rows, $id);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //更新角色信息
    public function updaterole()
    {
        $result = null;
        try {
            $obj = new \app\common\service\Action();
            $result = $obj->updateActionRole($_POST);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
} 