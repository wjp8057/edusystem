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
// | Created:2016/11/18 8:42
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyService;

class Testplan extends MyService {
    /**学生的课程考试安排
     * @param int $page
     * @param int $rows
     * @param $year
     * @param $term
     * @return array
     */
    public function studentCourseList($page=1,$rows=20,$year,$term,$studentno){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['testplan.year']=$year;
        $condition['testplan.term']=$term;
        $condition['r32.studentno']=$studentno;
        $data=$this->query->table('testplan')
            ->join('r32 ',' r32.year=testplan.year and r32.term=testplan.term and r32.courseno+r32.[group]=testplan.courseno')
            ->join('testbatch','testbatch.flag=testplan.flag and testbatch.year=testplan.year and testplan.term=testbatch.term')
            ->join('courses','courses.courseno=r32.courseno')
            ->field('testplan.year,testplan.term,testplan.courseno,courses.coursename,testbatch.testtime,roomno1,roomno2,roomno3')
            ->order('testtime')->page($page,$rows)->where($condition)->select();
        $count=$this->query->table('testplan')
                ->join('r32 ',' r32.year=testplan.year and r32.term=testplan.term and r32.courseno+r32.[group]=testplan.courseno')
                ->where($condition)->count();
        if(is_array($data)&&count($data)>0){ //小于0的话就不返回内容，防止IE下无法解析rows为NULL时的错误。
            $result=array('total'=>$count,'rows'=>$data);
        }
        return $result;
    }
}