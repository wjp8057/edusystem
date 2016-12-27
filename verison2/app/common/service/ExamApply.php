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
// | Created:2016/12/26 11:08
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyService;

class ExamApply extends MyService {
    //读取
    function getList($page=1,$rows=30,$map,$studentno='%',$studentname='%',$classno='%',$school='',$fee=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['map']=$map;
        if($studentno!='%') $condition['students.studentno']=array('like',$studentno);
        if($studentname!='%') $condition['students.name']=array('like',$studentname);
        if($classno!='%') $condition['students.classno']=array('like',$classno);
        if($school!='') $condition['classes.school']=$school;
        if($fee!='') $condition['examapplies.fee']=$fee;
        $data=$this->query->table('examapplies')
            ->join('students','students.studentno=examapplies.studentno')
            ->join('classes','classes.classno=students.classno')
            ->join('schools','schools.school=classes.school')
            ->page($page,$rows)->where($condition)
            ->field('students.studentno,students.name studentname,students.classno,rtrim(classes.classname) classname,
            schools.school,rtrim(schools.name) schoolname,fee')
            ->order('studentno')->select();
        $count= $this->query->table('examapplies')
            ->join('students','students.studentno=examapplies.studentno')
            ->join('classes','classes.classno=students.classno')
            ->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
    //更新
    function update($postData){
        $updateRow=0;
        $deleteRow=0;
        $insertRow=0;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["inserted"])) {
                $updated = $postData["inserted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $data = null;
                    $data['map'] = $one->map;
                    $data['year'] = $one->year;
                    $data['term'] = $one->term;
                    $data['fee'] = $one->fee;
                    $data['deadline'] = $one->deadline;
                    $data['dateofexam'] = $one->dateofexam;
                    $data['rem'] = $one->rem;
                    $row = $this->query->table('examnotification')->insert($data);
                    if ($row > 0)
                        $insertRow++;
                }
            }
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $data=null;
                    $condition['recno'] = $one->recno;
                    $data['fee'] = $one->fee;
                    $data['deadline'] = $one->deadline;
                    $data['dateofexam'] = $one->dateofexam;
                    $data['rem'] = $one->rem;
                    $data['lock'] = $one->lock;
                    $updateRow += $this->query->table('examnotification')->where($condition)->update($data);
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['recno'] = $one->recno;
                    $deleteRow += $this->query->table('examnotification')->where($condition)->delete();
                }
            }
        }
        catch(\Exception $e){
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        $info='';
        if($updateRow>0) $info.=$updateRow.'条更新！</br>';
        if($deleteRow>0) $info.=$deleteRow.'条删除！</br>';
        if($insertRow>0) $info.=$insertRow.'条添加！</br>';
        $status=1;
        if($info=='') {
            $info="没有数据被更新";
            $status=0;
        }
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }
}