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

namespace app\major\controller;

use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\Majorcode;
use app\common\service\Majordirection;

class Major extends MyController
{
      //读取专业代码
    public function query($page = 1, $rows = 20)
    {
        $result = null;
        try {
            $majorcode=new Majorcode();
            $result = $majorcode->getList($page, $rows);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //更新专业代码
    public function update()
    {
        $result = null;
        try {
            $majorcode=new Majorcode();
            $result = $majorcode->update($_POST);//无法用I('post.')获取二维数组
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
   //读取专业方向
    public function direction($page = 1, $rows = 20,$major)
    {
        $result = null;
        try {
            $direction=new Majordirection();
            $result = $direction->getList($page, $rows,$major);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //更新专业方向
    public function updatedirection()
    {
        $result = null;
        try {
            $direction=new Majordirection();
            $result = $direction->update($_POST);//无法用I('post.')获取二维数组
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //读取开设专业
    public function majorschool($page = 1, $rows = 20,$years='',$school='',$majorname='%')
    {
        $result = null;
        try {
            $major=new \app\common\service\Major();
            $result = $major->getList($page, $rows,$years,$school,$majorname);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }



} 