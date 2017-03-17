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
// | Created:2017/3/17 16:19
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyService;

class Sport extends MyService {
    function getList($page=1,$rows=20,$studentno='%',$year=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        if($studentno!='%') $condition['studentno']=array('like',$studentno);
        if($year!='') $condition['year']=$year;
        $data=$this->query->table('sport')->page($page,$rows)
            ->field('recno,year,studentno,grade,score,date')->where($condition)->order('year')->select();
        $count= $this->query->table('sport')->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
}