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
        try {
            $user=new \app\common\service\User();
            $result =$user->getUserList($page, $rows, $username, $name, $school);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    //更新信息
    public function update()
    {
        try {
            $user=new \app\common\service\User();
            $result =$user->updateUser($_POST);//无法用I('post.')获取二维数组
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    public function role()
    {
        try {
            $Obj = new Role();
            $result = $Obj->getRoleList();
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    //更新角色信息
    public function updaterole($username, $role = "*")
    {
        try {
            $user=new \app\common\service\User();
            $result =$user->updateUserRole($username, $role);//无法用I('post.')获取二维数组
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    /**修改本人密码
     * @param string $oldpwd
     * @param string $newpwd
     */
    public function resetself($oldpwd = '', $newpwd = '')
    {
        try {
            $user=new \app\common\service\User();
            $result =$user->changeSelfPassword($oldpwd, $newpwd);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    public function changepassword($teacherno, $password)
    {
        try {
            $user=new \app\common\service\User();
            $result =$user->changeUserPassword($teacherno, $password);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
} 