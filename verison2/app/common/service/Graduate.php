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

namespace app\common\service;


use app\common\access\MyAccess;
use app\common\access\MyService;

/**毕业审核详情
 * Class Graduate
 * @package app\common\service
 */
class Graduate extends MyService{

     //获取学生的某个培养方案的所有教学计划。
    function getProgram($majorplanid,$studentno){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['graduate.majorplanid']=$majorplanid;
        $condition['graduate.studentno']=$studentno;
        $data=$this->query->table('graduate')
            ->join('programs','programs.programno=graduate.programno')
            ->join('programform','programform.name=graduate.form')
            ->field('rowid,graduate.programno,rtrim(progname) progname,credits,mcredits,gcredits,rtrim(programform.value) formname ,form')
            ->where($condition)
            ->order('form,programno')->select();
        $count= $this->query->table('graduate')->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
    //获取某一个教学计划的未通过课程列表
    function getCourse($page = 1, $rows = 20,$studentno='%',$name='%',$classno='%',$school='',$form='',$rowid=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        if($studentno!='%') $condition['students.studentno']=array('like',$studentno);
        if($name!='%') $condition['students.name']=array('like',$name);
        if($classno!='%') $condition['students.classno']=array('like',$classno);
        if($school!='') $condition['classes.school']= $school;
        if($form!='') $condition['graduate.form']= $form;
        if($rowid!='') $condition['map']=$rowid;
        $data=$this->query->table('graduate')
            ->join('courses','courses.courseno=graduate.courseno')
            ->join('programs','programs.programno=graduate.programno')
            ->join('graduateform','graduateform.name=graduate.form')
            ->join('students','students.studentno=graduate.studentno')
            ->join('classes','classes.classno=students.classno')
            ->join('statusoptions','statusoptions.name=students.status')
            ->field('rtrim(students.name) name,students.studentno,rtrim(classes.classname) classname,courses.courseno,rtrim(courses.coursename) coursename,courses.credits,
            rtrim(programs.progname) progname,programs.programno,graduate.form,rtrim(graduateform.value) formname,rtrim(statusoptions.value) statusname')
            ->where($condition)->page($page,$rows)
            ->order('studentno,courseno')->select();
        $count= $this->query->table('graduate')
            ->join('students','students.studentno=graduate.studentno')
            ->join('classes','classes.classno=students.classno')
            ->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
} 