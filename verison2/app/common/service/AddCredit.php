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
// | Created:2016/11/19 13:00
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyService;

class AddCredit extends MyService {
    function getList($page=1,$rows=20,$year='',$term='',$studentno='%'){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        if($year!='')  $condition['year']=$year;
        if($term!='')  $condition['term']=$term;
        if($studentno!='%')$condition['studentno']=array('like',$studentno);
        $data=$this->query->table('addcredit')->page($page,$rows)
            ->where($condition)
            ->field('year,term,studentno,rtrim(reason) reason,credit,date')
            ->order('year,term,studentno')->select();
        $count= $this->query->table('addcredit')->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
    //获取指定学生的创新技能学分认定总计
    function getStudentSummary($studentno){
        $condition['studentno']=$studentno;
        $data=$this->query->table('addcredit')
            ->where($condition)
            ->field('rtrim(isnull(sum(credit),0)) total')
            ->find();

        return $data['total'];
    }
}