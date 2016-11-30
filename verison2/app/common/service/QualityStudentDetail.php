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
    //同步学生名单
    public function synStudent($year,$term,$courseno='%'){
        $row=0;
        try {
            MyAccess::checkAccess('E');
            $bind=["year"=>$year,"term"=>$term,"courseno"=>$courseno];
            //同步课程信息
            $sql="insert into qualitystudentdetail(year,term,map,studentno)
                select r32.year,r32.term,qualitystudent.id,r32.studentno
                from qualitystudent inner join r32 on r32.year=qualitystudent.year
                and r32.term=qualitystudent.term and r32.courseno+r32.[group]=qualitystudent.courseno
                where qualitystudent.year=:year and qualitystudent.term=:term and qualitystudent.courseno like :courseno
                and not exists (select * from qualitystudentdetail as d where d.map=qualitystudent.id and d.studentno=r32.studentno)";
            $row=Db::execute($sql,$bind);
        }
        catch(\Exception $e){
            throw $e;
        }
        return ["info"=>"同步学生成功！".$row."条记录添加","status"=>"1"];
    }
    //获取学生信息
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
    //更新学生名单
    public function  update($postData){
        $deleteRow=0;
        $insertRow=0;
        $errorRow=0;
        $info="";
        $status=1;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            $map=$postData["map"];
            if(MyAccess::checkQualityStudentSchool($map)) {
                if (isset($postData["inserted"])) {
                    $updated = $postData["inserted"];
                    $listUpdated = json_decode($updated);
                    foreach ($listUpdated as $one) {
                        $data = null;
                        $data['map'] = $map;
                        $data['studentno'] = $one->studentno;
                        $insertRow += $this->query->table('qualitystudentdetail')->insert($data);
                    }
                }
                //删除部分
                if (isset($postData["deleted"])) {
                    $updated = $postData["deleted"];
                    $listUpdated = json_decode($updated);
                    foreach ($listUpdated as $one) {
                        $condition = null;
                        $condition['id'] = $one->id;
                        $deleteRow += $this->query->table('qualitystudentdetail')->where($condition)->delete();
                    }
                }
            }
            else
            {
                $info= '您修改其他学院考评课程的学生！';
                $status=0;
            }
        }
        catch(\Exception $e){
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        if($deleteRow+$insertRow+$errorRow==0){
            $status=0;
            $info="没有数据更新";
        }
        else {
            if ($deleteRow > 0) $info .= $deleteRow . '条删除！</br>';
            if ($insertRow > 0) $info .= $insertRow . '条添加！</br>';
        }
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }
}