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
// | Created:2017/3/17 10:53
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\Item;
use app\common\access\MyAccess;
use app\common\access\MyService;
//学分顶替
class Instead  extends  MyService{

    function getList($page=1,$rows=20,$studentno='%',$courseno='%',$eqcourseno='%',$school=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        if($studentno!='%') $condition['students.studentno']=array('like',$studentno);
        if($courseno!='%') $condition['instead.courseno']=array('like',$courseno);
        if($eqcourseno!='%') $condition['instead.eqcourseno']=array('like',$eqcourseno);
        if($school!='') $condition['classes.school']=$school;
        $data=$this->query->table('instead')->page($page,$rows)
            ->join('students','students.studentno=instead.studentno')
            ->join('classes','classes.classno=students.classno')
            ->join('schools','schools.school=classes.school')
            ->join('courses','courses.courseno=instead.courseno')
            ->join('courses ec','ec.courseno=instead.eqcourseno')
            ->join('schools cs','cs.school=courses.school')
            ->join('schools ecs','ecs.school=ec.school')
            ->field('id,students.studentno,rtrim(students.name) studentname,rtrim(classes.classname) classname,rtrim(schools.name) schoolname,courses.courseno,rtrim(courses.coursename) coursename,
            rtrim(cs.name) courseschoolname,ec.courseno eqcourseno,rtrim(ec.coursename) eqcoursename,rtrim(ecs.name) eqcourseschoolname,courses.credits,ec.credits eqcredits,classes.school studentschool')
            ->where($condition)->order('id')->select();
        $count= $this->query->table('instead')->page($page,$rows)
            ->join('students','students.studentno=instead.studentno')
            ->join('classes','classes.classno=students.classno')->where($condition)
            ->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

    /**更新
     * @param $postData
     * @return array
     * @throws \Exception
     */
    function update($postData){
        //检查输入有效性
        $result=null;
        if (isset($postData["inserted"])) {
            $updated = $postData["inserted"];
            $updated= json_decode($updated);
            if(MyAccess::checkStudentSchool($updated->studentno)) {
                Item::getCourseItem($updated->courseno);
                Item::getCourseItem($updated->eqcourseno);
                $data['studentno'] = $updated->studentno;
                $data['courseno'] = $updated->courseno;
                $data['eqcourseno'] = $updated->eqcourseno;
                $this->query->table('instead')->insert($data);
                $result = ['status' => 1, 'info' => '添加成功'];
            }
            else{
                $result = ['status' => 0, 'info' => '您无法为其他学院学生添加课程顶替'];
            }
        }

        if (isset($postData["deleted"])) {
            $updated = $postData["deleted"];
            $updated= json_decode($updated);
            if(MyAccess::checkStudentSchool($updated->studentno)) {
                $condition['id'] = $updated->id;
                $condition['studentno'] = $updated->studentno;
                $this->query->table('instead')->where($condition)->delete();
                $result = ['status' => 1, 'info' => '删除成功'];
            }
            else
                $result = ['status' => 0, 'info' => '您无法删除其他学院教学计划的等价课程'];
        }
        return $result;
    }


}