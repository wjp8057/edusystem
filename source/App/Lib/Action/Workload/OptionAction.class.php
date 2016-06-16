<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/5/14
 * Time: 9:57
 */
class OptionAction extends Action {
    public function school(){
        $Obj=M('schools');
        $result=$Obj->field('school,name')->where('active=1')->select();
        $array[] = array('school' => '%', 'name' => '全部');
        $result = array_merge($array,$result);
        $this->ajaxReturn($result,'JSON');
    }
    public function worktype(){
        $Obj=M('worktype');
        $result=$Obj->field('type,name,rank')->order('rank')->select();
        $this->ajaxReturn($result,'JSON');
    }
    public function workteachertype(){
        $Obj=M('workteachertype');
        $result=$Obj->field('type,name')->select();
        $this->ajaxReturn($result,'JSON');
    }
    public function jobtype(){
        $Obj=M('teacherjob');
        $result=$Obj->field('job,name')->select();
        $array[] = array('job' => '%', 'name' => '全部');
        $result = array_merge($array,$result);
        $this->ajaxReturn($result,'JSON');
    }
    public function teachertype(){
        $Obj=M('teachertype');
        $result=$Obj->field('value,name')->select();
        $array[] = array('name' => '%', 'value' => '全部');
        $result = array_merge($array,$result);
        $this->ajaxReturn($result,'JSON');
    }

    public function position(){
        $Obj=M('positions');
        $result=$Obj->field('value,name')->select();
        $this->ajaxReturn($result,'JSON');
    }
}