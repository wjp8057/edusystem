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


use app\common\access\Item;
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
                where qualitystudent.year=scheduleplan.year and qualitystudent.term=scheduleplan.term and qualitystudent.courseno=scheduleplan.courseno+scheduleplan.[group] and teacherplan.teacherno=qualitystudent.teacherno)";
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

    public function  update($postData){
        $updateRow=0;
        $deleteRow=0;
        $insertRow=0;
        $errorRow=0;
        $info="";
        $status=1;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["inserted"])) {
                $updated = $postData["inserted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $data = null;
                    $data['courseno'] = $one->courseno;
                    $data['year'] = $one->year;
                    $data['term'] = $one->term;
                    $data['type'] = $one->type;
                    $data['teacherno'] = $one->teacherno;
                    $school=Item::getCourseItem( $one->courseno)['school'];
                    $data['school'] = $school;
                    if ($school!= session('S_USER_SCHOOL') && session('S_MANAGE') == 0) {
                        $info .= '无法为其它学院添加课程条目'.$one->courseno .'</br>';
                        $status=0;
                        $errorRow++;
                    }
                    else {
                        $row = $this->query->table('qualitystudent')->insert($data);
                        if ($row > 0)
                            $insertRow++;
                    }
                }
            }
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $data = null;
                    $condition['id'] = $one->id;
                    $data['enabled'] = $one->enabled;
                    $data['type'] = $one->type;

                    if(MyAccess::checkQualityStudentSchool($one->id)) {
                        $updateRow += $this->query->table('qualitystudent')->where($condition)->update($data);
                        $condition = null;
                        $data = null;
                        $data['enabled'] = $one->enabled;
                        $condition['map'] = $one->id;
                        $this->query->table('qualitystudentdetail')->where($condition)->update($data);
                    }
                    else{
                        $info.=$one->courseno.'不是本学院班级，无法更改信息</br>';
                        $errorRow++;
                        $status=0;
                    }

                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['id'] = $one->id;
                    if(MyAccess::checkQualityStudentSchool($one->id)) {
                        $deleteRow += $this->query->table('qualitystudent')->where($condition)->delete();
                        $condition = null;
                        $data = null;
                        $condition['map'] = $one->id;
                        $this->query->table('qualitystudentdetail')->where($condition)->delete();
                    }
                    else{
                        $info.=$one->courseno.'不是本学院课程条目，无法删除</br>';
                        $errorRow++;
                        $status=0;
                    }
                }
            }
        }
        catch(\Exception $e){
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        if($updateRow+$deleteRow+$insertRow+$errorRow==0){
            $status=0;
            $info="没有数据更新";
        }
        else {
            if ($updateRow > 0) $info .= $updateRow . '条更新！</br>';
            if ($deleteRow > 0) $info .= $deleteRow . '条删除！</br>';
            if ($insertRow > 0) $info .= $insertRow . '条添加！</br>';
        }
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }
}