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
    function getCourse($rowid){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['map']=$rowid;
        $data=$this->query->table('graduate')
            ->join('courses','courses.courseno=graduate.courseno')
            ->field('courses.courseno,rtrim(courses.coursename) coursename,courses.credits,graduate.form')
            ->where($condition)
            ->order('courseno')->select();
        $count= $this->query->table('graduate')->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
} 