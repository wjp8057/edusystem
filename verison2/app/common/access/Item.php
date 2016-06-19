<?php
// +----------------------------------------------------------------------
// |  [ MAKE YOUR WORK EASIER]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2016 http://www.nbcc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: fangrenfu <fangrenfu@126.com> 2016/6/19 10:20
// +----------------------------------------------------------------------

namespace app\common\access;

use think\Db;
use think\Exception;

/**获取各种对象的关键属性
 * Class Item
 * @package app\common\access
 */
class Item {
    /**根据教室号获取教室信息
     * @param $roomno
     * @param bool $alert
     * @return null
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public static function  getRoomItem($roomno,$alert=true){
        $condition=null;
        $result=null;
        $condition['roomno']=$roomno;
        $data=Db::table('classrooms')->where($condition)->field('rtrim(jsn) name,status,reserved')->select();
        if(!is_array($data)||count($data)!=1) {
            if($alert)
                throw new Exception('roomno:' . $roomno, MyException::ITEM_NOT_EXISTS);
        }
        else
            $result=$data[0];
        return $result;
    }

    /**根据课号获取课程
     * @param string $courseno
     * @param bool $alert
     * @return null
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public static function getCourseItem($courseno='',$alert=true){
        $result=null;
        $condition=null;
        $condition['courseno']=$courseno;
        $data=Db::table('courses')->where($condition)->field('rtrim(coursename) as coursename')->select();
        if(!is_array($data)||count($data)!=1) {
            if($alert)
                throw new Exception('courseno:' . $courseno, MyException::ITEM_NOT_EXISTS);
        }
        else
            $result=$data[0];
        return $result;
    }

    public static function getProgramItem($programno='',$alert=true){
        $result=null;
        $condition=null;
        $condition['courseno']=$programno;
        $data=Db::table('courses')->where($condition)->field('rtrim(coursename) as coursename')->select();
        if(!is_array($data)||count($data)!=1) {
            if($alert)
                throw new Exception('programno:' . $programno, MyException::ITEM_NOT_EXISTS);
        }
        else
            $result=$data[0];
        return $result;
    }
} 