<?php
// +----------------------------------------------------------------------
// |  [ MAKE YOUR WORK EASIER]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2016 http://www.nbcc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: fangrenfu <fangrenfu@126.com> 2016/6/19 8:48
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyService;

class Program extends MyService{
    function equalCourseList($page=1,$rows=20,$courseno='%',$equalcourseno='%',$programno='%',$school=''){
        $result=null;
        $condition=null;
        if($courseno!='%') $condition['r33.courseno']=array('like',$courseno);
        if($equalcourseno!='%') $condition['r33.eqno']=array('like',$equalcourseno);
        if($programno!='%') $condition['r33.programno']=array('like',$programno);
        if($school!='') $condition['programs.school']=$school;
        $data= $this->query->table('programs')->join('r33','r33.programno=programs.programno')
            ->join('courses c','c.courseno=r33.courseno')
            ->join('courses eqc','eqc.courseno=r33.eqno')
            ->join('schools cs','cs.school=c.school')
            ->join('schools eqs','eqs.school=eqc.school')
            ->join('schools s','s.school=programs.school')
            ->field('programs.programno,rtrim(progname) progname,rtrim(s.name) progschoolname,c.courseno,rtrim(c.coursename) coursename,
            c.credits,rtrim(cs.name) schoolname,eqc.courseno eqcourseno,rtrim(eqc.coursename) eqcoursename,eqc.credits eqcredits,
            rtrim(eqs.name) eqschoolname')
            ->where($condition)->page($page,$rows)->order('programno')->select();
        $count= $this->query->table('programs')->join('r33','r33.programno=programs.programno')->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
} 