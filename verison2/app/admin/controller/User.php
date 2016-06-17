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
use app\common\service\Role;

class User extends MyController
{

    //显示信息
    public function query($page = 1, $rows = 10, $username = '%', $name = '%', $school = '')
    {
        $result = null;
        try {
            $user = new \app\common\service\User();
            $result = $user->getUserList($page, $rows, $username, $name, $school);

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
            $user = new \app\common\service\User();
            $result = $user->updateUser($_POST);//无法用I('post.')获取二维数组

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function role()
    {
        $result = null;
        try {
            $Obj = new Role();
            $result = $Obj->getRoleList();

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //更新角色信息
    public function updaterole($username, $role = "*")
    {
        $result = null;
        try {
            $user = new \app\common\service\User();
            $result = $user->updateUserRole($username, $role);//无法用I('post.')获取二维数组

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    /**修改本人密码
     * @param string $oldpwd
     * @param string $newpwd
     */
    public function resetself($oldpwd = '', $newpwd = '')
    {
        $result = null;
        try {
            $user = new \app\common\service\User();
            $result = $user->changeSelfPassword($oldpwd, $newpwd);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function changepassword($teacherno, $password)
    {
        $result = null;
        try {
            $user = new \app\common\service\User();
            $result = $user->changeUserPassword($teacherno, $password);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
} 