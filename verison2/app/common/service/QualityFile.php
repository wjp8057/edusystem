<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 11:03
 */

namespace app\common\service;


use app\common\access\MyService;
use app\common\access\MyException;
use think\Exception;


class QualityFile extends MyService{
    /**
     * @param int $page
     * @param int $rows
     * @param string $teacherno
     * @return array|null
     * @throws \think\Exception
     */
    function getTeacherQualityList($page=1,$rows=20,$teacherno=''){
        if($teacherno=='')
            throw new Exception('teacherno is empty ', MyException::PARAM_NOT_CORRECT);

        $result=null;
        $condition=null;
        $condition['teacherno']=$teacherno;
        $count= $this->query->table('qualityfile')->where($condition)->count();// 查询满足要求的总记录数
        $data=$this->query->table('qualityfile')->where($condition)->page($page,$rows)->order('year desc,term desc')->select();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
}