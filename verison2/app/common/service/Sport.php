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
    function getList($page=1,$rows=20,$studentno='%',$year='',$classno='%',$school='',$score=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        if($studentno!='%') $condition['sport.studentno']=array('like',$studentno);
        if($year!='') $condition['sport.year']=$year;
        if($classno!='%') $condition['students.classno']=array('like',$classno);
        if($school!='') $condition['classes.school']=$school;
        if($score!='') $condition['sport.score']=array('lt',$score);
        $data=$this->query->table('sport')->page($page,$rows)
            ->join('students','students.studentno=sport.studentno')
            ->join('classes','classes.classno=students.classno')
            ->join('schools','schools.school=classes.school')
            ->field('recno,sport.year,students.studentno,sport.grade,sport.score,sport.date,rtrim(classes.classname) classname,rtrim(students.name) studentname,
            rtrim(schools.name) schoolname')->where($condition)->order('year,studentno')->select();
        $count= $this->query->table('sport')
            ->join('students','students.studentno=sport.studentno')
            ->join('classes','classes.classno=students.classno')
            ->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
}