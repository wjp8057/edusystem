<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:51
 */

namespace app\common\service;


use app\common\access\MyAccess;
use app\common\access\MyException;
use app\common\access\MyService;

class Course  extends  MyService{
    /*
 * 根据课号获取课名
 */
    function getNameByCourseNo($courseno=''){
        $result=null;
        $condition=null;
        $condition['courseno']=$courseno;
        $data=$this->query->table('courses')->where($condition)->field('rtrim(coursename) as coursename')->find();
        if(!is_array($data)||count($data)!=1)
            throw new \think\Exception('courseno'.$courseno, MyException::PARAM_NOT_CORRECT);
        return $data[0]['coursename'];
    }

}