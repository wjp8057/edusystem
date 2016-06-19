<?php
// +----------------------------------------------------------------------
// |  [ MAKE YOUR WORK EASIER]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2016 http://www.nbcc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: fangrenfu <fangrenfu@126.com> 2016/6/19 9:57
// +----------------------------------------------------------------------

namespace app\all\controller;


use app\common\access\Item;
use app\common\access\MyAccess;

class Info
{
    public function  getcoursename($courseno)
    {
        $result = null;
        $status = 1;
        try {
            dump($courseno);
            $result = Item::getCourseItem($courseno, false);
            if ($result != null) {
                $result = $result['coursename'];
                $status = 1;
            } else {
                $result = '该课程不存在!';
                $status = 0;
            }
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json(["status" => $status, 'info' => $result]);
    }

    public function getroomname($roomno)
    {
        $result = null;
        $status = 1;
        try {
            $result = Item::getRoomItem($roomno);
            if ($result != null) {
                $result = $result['name'];
                $status = 1;
            } else {
                $result = '该教室不存在!';
                $status = 0;
            }
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json(["status" => $status, 'info' => $result]);
    }
} 