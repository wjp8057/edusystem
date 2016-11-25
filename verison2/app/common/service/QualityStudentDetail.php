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
// | Created:2016/11/25 15:54
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyAccess;
use app\common\access\MyService;
use think\Db;

class QualityStudentDetail extends MyService{
    public function synStudent($year,$term,$courseno='%'){
        $row=0;
        try {
            MyAccess::checkAccess('E');
            $bind=["year"=>$year,"term"=>$term,"courseno"=>$courseno];
            //同步课程信息
            $sql="insert into qualitystudentdetail(map,studentno)
                select qualitystudent.id,r32.studentno
                from qualitystudent inner join r32 on r32.year=qualitystudent.year
                and r32.term=qualitystudent.term and r32.courseno+r32.[group]=qualitystudent.courseno
                where qualitystudent.year=2016 and qualitystudent.term=1 and not exists (select
                * from qualitystudentdetail as d where d.map=qualitystudent.id and d.studentno=r32.studentno)";
            $row=Db::execute($sql,$bind);
        }
        catch(\Exception $e){
            throw $e;
        }
        return ["info"=>"同步学生成功！".$row."条记录添加","status"=>"1"];
    }
    function getList($page=1,$rows=20,$map){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['map']=$map;
        $data=$this->query->table('qualitystudentdetail')->page($page,$rows)
            ->join('students','students.studentno=qualitystudentdetail.studentno')
            ->join('classes','classes.classno=students.classno')
            ->join('schools','schools.school=classes.school')
            ->join('statusoptions','statusoptions.name=students.status')
            ->where($condition)
            ->field('id,map,students.studentno,rtrim(students.name) studentname,rtrim(classes.classname)  classname,rtrim(schools.name) schoolname,
            rtrim(statusoptions.value) statusname')
            ->order('studentno')->select();
        $count= $this->query->table('qualitystudentdetail')
            ->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
}