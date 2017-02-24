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


use app\common\access\Item;
use app\common\access\MyException;
use app\common\access\MyService;
use think\Exception;

class ExamApply extends MyService {
    //读取基本信息
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
            ->join('personal','students.studentno=personal.studentno')
            ->join('classes','classes.classno=students.classno')
            ->join('schools','schools.school=classes.school')
            ->page($page,$rows)->where($condition)
            ->field('students.studentno,students.name studentname,students.classno,rtrim(classes.classname) classname,
            schools.school,rtrim(schools.name) schoolname,fee,personal.id')
            ->order('studentno')->select();
        $count= $this->query->table('examapplies')
            ->join('students','students.studentno=examapplies.studentno')
            ->join('classes','classes.classno=students.classno')
            ->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
    //读取最详细的信息 用于导出
    function getListDetail($page=1,$rows=30,$map,$studentno='%',$studentname='%',$classno='%',$school='',$fee=''){
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
            ->join('personal','students.studentno=personal.studentno')
            ->join('classes','classes.classno=students.classno')
            ->join('schools','schools.school=classes.school')
            ->join('sexcode','sexcode.code=students.sex')
            ->join('nationalitycode nc','nc.code=personal.nationality')
            ->join('viewgrademaxscore as v','v.studentno=students.studentno')
            ->page($page,$rows)->where($condition)
            ->field('students.studentno,rtrim(students.name) studentname,students.classno,rtrim(classes.classname) classname,
            schools.school,rtrim(schools.name) schoolname,fee,personal.id,pretcoa,pretcob,cet3,cet4,rtrim(nc.name) nationalityname,
            rtrim(sexcode.name) sexname,CONVERT(varchar(100), birthday, 23) birthday,students.years,DATEPART(year,classes.year) grade')
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
        $deleteRow=0;
        $insertRow=0;
        //更新部分
        //开始事务
        $map=0;
        if (isset($postData["map"]))
        {
            $map=$postData["map"];
            $valid=Item::getExamNotificationItem($map);
            $valid['now']=date("Y-m-d H:m:s");
            if(strtotime($valid['now'])>strtotime($valid['deadline']))
            {
                return array('info'=>'报名已截止','status'=>0);
            }
        }
        else
        {
            throw new Exception('undefined paramter:map',MyException::PARAM_NOT_CORRECT);
        }
        $this->query->startTrans();
        try {
            if (isset($postData["inserted"])) {
                $updated = $postData["inserted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $data = null;
                    $data['map'] = $map;
                    $data['fee'] = 1;
                    $data['studentno'] = $one->studentno;
                    $row = $this->query->table('examapplies')->insert($data);
                    if ($row > 0)
                        $insertRow++;
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['map'] = $map;
                    $condition['studentno'] = $one->studentno;
                    $deleteRow += $this->query->table('examapplies')->where($condition)->delete();
                }
            }
        }
        catch(\Exception $e){
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        $info='';
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
    //检索学生信息，包含各级成绩
    function searchStudent($page=1,$rows=30,$studentno='%',$studentname='%',$classno='%',$school=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        if($studentno!='%') $condition['students.studentno']=array('like',$studentno);
        if($studentname!='%') $condition['students.name']=array('like',$studentname);
        if($classno!='%') $condition['students.classno']=array('like',$classno);
        if($school!='') $condition['classes.school']=$school;
        $data=$this->query->table('students')
            ->join('classes','classes.classno=students.classno')
            ->join('schools','schools.school=classes.school')
            ->join('viewgrademaxscore as v','v.studentno=students.studentno')
            ->page($page,$rows)->where($condition)
            ->field('students.studentno,students.name studentname,students.classno,rtrim(classes.classname) classname,
            schools.school,rtrim(schools.name) schoolname,pretcoa,pretcob,cet3,cet4')
            ->order('studentno')->select();
        $count= $this->query->table('students')
            ->join('classes','classes.classno=students.classno')
            ->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
}