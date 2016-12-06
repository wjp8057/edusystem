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
// | Created:2016/12/5 11:15
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyService;
use think\Db;

class R32Dump extends MyService {
    //获取被筛选的课程信息
    public function getList($page=1,$rows=20,$year,$term,$studentno){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['r32dump.year']=$year;
        $condition['r32dump.term']=$term;
        $condition['r32dump.studentno']=$studentno;
        $data=$this->query->table('r32dump')
            ->join('courses','courses.courseno=r32dump.courseno')
            ->join('plantypecode','plantypecode.code=r32dump.coursetype')
            ->join('examoptions','examoptions.name=r32dump.examtype')
            ->where($condition)->page($page,$rows)
            ->field("r32dump.year,term,r32dump.courseno+[group] courseno,rtrim(courses.coursename) coursename,reason,selecttime,courses.credits,examoptions.value examtype,plantypecode.name coursetype")
            ->order('year desc,term desc,courseno')
            ->select();
        $count= $this->query->table('r32dump')->where($condition)->count();// 查询满足要求的总记录数
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
    //备份删除记录
    public static function toDump($year,$term,$courseno,$studentno='%',$reason='未知'){
        $bind=["year"=>$year,"term"=>$term,'courseno'=>$courseno,'studentno'=>$studentno,'reason'=>$reason];
        $sql="insert into r32dump(year,term,courseno,[group],studentno,inprogram,conflicts,confirm,approach,repeat,fee,coursetype,examtype,selecttime,reason)
              select year,term,courseno,[group],studentno,inprogram,conflicts,confirm,approach,repeat,fee,coursetype,examtype,selecttime,:reason
              from r32
              where year=:year and term=:term and courseno+[group]=:courseno and studentno like :studentno";
        Db::execute($sql,$bind);
    }
}