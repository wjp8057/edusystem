<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:51
 */

namespace app\common\service;


use app\common\access\MyAccess;
use app\common\access\MyException;
use app\common\access\MyService;

class Course  extends  MyService{
    /**根据课号获取课名
     * @param string $courseno
     * @return mixed
     * @throws \think\Exception
     */
    function getItemByCourseNo($courseno=''){
        $result=null;
        $condition=null;
        $condition['courseno']=$courseno;
        $data=$this->query->table('courses')->where($condition)->field('rtrim(coursename) as coursename')->select();
        if(!is_array($data)||count($data)!=1)
            throw new \think\Exception('courseno'.$courseno, MyException::PARAM_NOT_CORRECT);
        return $data[0];
    }

    function getList($page=1,$rows=20,$courseno='%',$coursename='%',$school=''){
        $result=null;
        $condition=null;
        if($courseno!='%') $condition['courses.courseno']=array('like',$courseno);
        if($coursename!='%') $condition['courses.coursename']=array('like',$coursename);
        if($school!='') $condition['courses.school']= $school;

        $data=$this->query->table('courses')->join('schools ','schools.school=courses.school')
            ->join('coursetypeoptions','coursetypeoptions.name=courses.type')
            ->join('courseform','courseform.name=courses.form','LEFT')
            ->page($page,$rows)
            ->field('rtrim(courses.courseno) courseno,rtrim(courses.coursename) coursename,courses.total,credits,hours,
            lhours,experiments,computing,khours,shours,zhours,week,rtrim(schools.name) schoolname,schools.school,
            rtrim(coursetypeoptions.value) typename,courses.type,rtrim(courseform.value) formname,courses.form')
            ->where($condition)->order('courseno')->select();
        $count= $this->query->table('courses')->where($condition)->count();
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
                    $data['coursename'] = $one->coursename;
                    $data['school'] = $one->school;
                    $data['total'] = $one->total;
                    $data['credits'] = $one->credits;
                    $data['week'] = $one->week;
                    $data['hours'] = $one->hours;
                    $data['lhours'] = $one->lhours;
                    $data['experiments'] = $one->experiments;
                    $data['computing'] = $one->computing;
                    $data['khours'] = $one->khours;
                    $data['shours'] = $one->shours;
                    $data['zhours'] = $one->zhours;
                    $data['type'] = $one->type;
                    $data['form'] = $one->form;
                    if ($data['school'] != session('S_USER_SCHOOL') && session('S_MANAGE') == 0) {
                        $info .= '无法为其它学院添加课程'.$one->coursename .'</br>';
                        $status=0;
                        $errorRow++;
                    }
                    else {
                        $row = $this->query->table('courses')->insert($data);
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
                    $condition['courseno'] = $one->courseno;
                    $data['coursename'] = $one->coursename;
                    $data['school'] = $one->school;
                    $data['total'] = $one->total;
                    $data['credits'] = $one->credits;
                    $data['week'] = $one->week;
                    $data['hours'] = $one->hours;
                    $data['lhours'] = $one->lhours;
                    $data['experiments'] = $one->experiments;
                    $data['computing'] = $one->computing;
                    $data['khours'] = $one->khours;
                    $data['shours'] = $one->shours;
                    $data['zhours'] = $one->zhours;
                    $data['type'] = $one->type;
                    $data['form'] = $one->form;
                    if(MyAccess::checkCourseSchool($one->courseno))
                        $updateRow += $this->query->table('courses')->where($condition)->update($data);
                    else{
                        $info.=$one->name.'不是本学院课程，无法更改信息</br>';
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
                    $condition['courseno'] = $one->courseno;
                    if(MyAccess::checkCourseSchool($one->courseno))
                        $deleteRow += $this->query->table('courses')->where($condition)->delete();
                    else{
                        $info.=$one->name.'不是本学院课程，无法删除</br>';
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