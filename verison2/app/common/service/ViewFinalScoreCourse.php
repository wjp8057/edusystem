<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:22
 */

namespace app\common\service;


use app\common\access\MyException;
use app\common\access\MyService;
use think\Exception;

class ViewFinalScoreCourse extends MyService{
    function getList($page=1,$rows=20,$year='',$term='',$courseno='%',$coursename='%',$school=''){
        if($year==''||$term=='')
            throw new Exception('year or term is empty', MyException::PARAM_NOT_CORRECT);

        $result=null;
        $condition=null;
        $condition['year']=$year;
        $condition['term']=$term;
        if($courseno!='%') $condition['courseno']=array('like',$courseno);
        if($coursename!='%') $condition['coursename']=array('like',$coursename);
        if($school!='') $condition['school']=$school;
        $count= $this->query->table('viewfinalscorecourse')->where($condition)->count();// 查询满足要求的总记录数
        $data=$this->query->table('viewfinalscorecourse')->where($condition)->page($page,$rows)
            ->field("courseno,rtrim(coursename) coursename,year,term,school,rtrim(schoolname) schoolname,total,uninput")->order('uninput desc,school,courseno')->select();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

}