<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 14:17
 */

namespace app\common\service;


use app\common\access\MyService;

class WorkFile extends  MyService {
    function getTeacherWorkList($page=1,$rows=20,$teacherno){
        $result=null;
        $condition=null;
        $condition['teacherno']=$teacherno;
        $count= $this->query->table('workfile')->where($condition)->count();// 查询满足要求的总记录数
        $data=$this->query->table('workfile')->where($condition)->page($page,$rows)->order('year desc,term desc')->select();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

}