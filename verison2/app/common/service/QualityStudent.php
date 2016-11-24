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
// | Created:2016/11/24 15:08
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyAccess;
use app\common\access\MyService;
use think\Db;
//学评教课程条目信息
class QualityStudent extends MyService {
    //同步课程信息
    public function synCourse($year,$term,$courseno='%'){
        $row=0;
        try {
            MyAccess::checkAccess('E');
            $bind=["year"=>$year,"term"=>$term,"courseno"=>$courseno];
            //同步课程信息
            $sql="insert into qualitystudent(year,term,teacherno,type,courseno,school)
                select scheduleplan.year,scheduleplan.term ,teacherplan.teacherno,courses.form,scheduleplan.courseno+scheduleplan.[group] as courseno,courses.school
                from scheduleplan
                inner join teacherplan on teacherplan.map=scheduleplan.recno
                inner join courses on courses.courseno=scheduleplan.courseno
                where scheduleplan.year=:year and scheduleplan.term=:term and teacherplan.teacherno!='000000' and scheduleplan.courseno+scheduleplan.[group] like :courseno and
                not exists (select * from qualitystudent
                where qualitystudent.year=scheduleplan.year and qualitystudent.term=scheduleplan.term and qualitystudent.courseno=scheduleplan.courseno+scheduleplan.[group] and qualitystudent.type=courses.form)";
            $row=Db::execute($sql,$bind);
            //设定毕业实践环节课程
            $sql="update qualitystudent
            set type='D'
            from qualitystudent inner join courses on courses.courseno=substring(qualitystudent.courseno,1,7)
            where courses.worktype in ('C','D') and year=:year and term=:term and qualitystudent.courseno like :courseno";
            Db::execute($sql,$bind);
        }
        catch(\Exception $e){
            throw $e;
        }
        return ["info"=>"同步课程成功！".$row."条记录添加","status"=>"1"];
    }
    //设定锁定状态
    public function setCourseStatus($year,$term,$courseno='%',$lock=1){
        $row=0;
        $info=$lock==1?'锁定':'解锁';
        try {
            $data['lock']=$lock;
            $condition['year']=$year;
            $condition['term']=$term;
            $condition['courseno']=array('like',$courseno);
            $row=$this->query->table('qualitystudent')->where($condition)->update($data);
        }
        catch(\Exception $e){
            throw $e;
        }
        return ["info"=>"锁定成功！".$row."条记录".$info,"status"=>"1"];
    }

    function getList($page=1,$rows=20,$year,$term,$courseno='%',$coursename='%',$teachername='%',$school='',$type='',$enabled='',$lock=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['year']=$year;
        $condition['term']=$term;
        if($courseno!='%')$condition['qualitystudent.courseno']=array('like',$courseno);
        if($teachername!='%')$condition['teachers.name']=array('like',$teachername);
        if($coursename!='%')$condition['courses.coursename']=array('like',$coursename);
        if($school!='')$condition['qualitystudent.school']=$school;
        if($type!='')$condition['qualitystudent.type']=$type;
        if($enabled!='')$condition['qualitystudent.enabled']=$enabled;
        if($lock!='')$condition['qualitystudent.lock']=$lock;
        $data=$this->query->table('qualitystudent')->page($page,$rows)
            ->join('schools','schools.school=qualitystudent.school')
            ->join('courses','courses.courseno=substring(qualitystudent.courseno,1,7)')
            ->join('qualitystudenttype','qualitystudenttype.type=qualitystudent.type')
            ->join('teachers','teachers.teacherno=qualitystudent.teacherno')
            ->where($condition)
            ->field('qualitystudent.id,qualitystudent.year,qualitystudent.term,qualitystudent.teacherno,rtrim(teachers.name) teachername,qualitystudent.type,
            rtrim(qualitystudenttype.name) typename,qualitystudent.courseno,rtrim(courses.coursename) coursename,qualitystudent.school,rtrim(schools.name) schoolname,
            qualitystudent.enabled,qualitystudent.lock')
            ->order('year,term,courseno,teacherno')->select();
        $count= $this->query->table('qualitystudent')
            ->join('teachers','teachers.teacherno=qualitystudent.teacherno')
            ->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
}