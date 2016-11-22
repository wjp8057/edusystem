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
// | Created:2016/11/19 13:50
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyAccess;
use app\common\access\MyService;

class CreditApply extends MyService {
    //读取学分认定信息
    function getList($page=1,$rows=20,$year='',$term='',$studentno='%',$reason='%',$school='',$type='',$audit='',$verify=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        if($year!='')  $condition['creditapply.year']=$year;
        if($term!='')  $condition['term']=$term;
        if($school!='') $condition['classes.school']=$school;
        if($reason!='%')$condition['reason']=array('like',$reason);
        if($studentno!='%')$condition['creditapply.studentno']=array('like',$studentno);
        if($type!='') $condition['creditapply.type']=$type;
        if($audit!='')  $condition['audit']=$audit;
        if($verify!='')  $condition['verify']=$verify;
        $data=$this->query->table('creditapply')->page($page,$rows)
            ->join('students','students.studentno=creditapply.studentno')
            ->join('classes','students.classno=classes.classno')
            ->join('schools','schools.school=classes.school')
            ->join('credittype','credittype.type=creditapply.type')
            ->where($condition)
            ->field('creditapply.id,creditapply.year,creditapply.term,students.studentno,rtrim(reason) reason,credit,cerdate,applydate,schools.school,rtrim(schools.name) schoolname,
            rtrim(classes.classname) classname,rtrim(students.name) studentname,audit,verify,rtrim(credittype.name) typename,creditapply.type,filedate')
            ->order('year,term,studentno')->select();
        $count= $this->query->table('creditapply')
            ->join('students','students.studentno=creditapply.studentno')
            ->join('classes','students.classno=classes.classno')
            ->join('schools','schools.school=classes.school')->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
    //更改学分认定的申请信息
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
                $data = null;
                foreach ($listUpdated as $one) {
                    $data['year'] = get_year_term()['year'];
                    $data['term'] = get_year_term()['term'];
                    $data['credit']=$one->credit;
                    $data['reason']=$one->reason;
                    $data['type']=$one->type;
                    $data['cerdate']=$one->cerdate;
                    $data['school']=session('S_USER_SCHOOL');
                    $data['studentno']=session('S_USER_NAME');
                    $row = $this->query->table('creditapply')->insert($data);
                    if ($row > 0)
                        $insertRow++;
                }
            }
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['id'] = $one->id;
                    $condition['audit'] = 0;
                    $condition['studentno'] =session('S_USER_NAME');
                    $data['credit']=$one->credit;
                    $data['reason']=$one->reason;
                    $data['cerdate']=$one->cerdate;
                    $data['type']=$one->type;
                    $updateRow += $this->query->table('creditapply')->where($condition)->update($data);
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['id'] = $one->id;
                    $condition['studentno'] =session('S_USER_NAME');
                    $condition['audit'] = 0;
                    $deleteRow += $this->query->table('creditapply')->where($condition)->delete();
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
    //审核学分认定信息
    function audit($postData){
        $updateRow=0;
        $deleteRow=0;
        $insertRow=0;
        $errorRow=0;
        $info='';
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['id'] = $one->id;
                    $condition['verify'] = 0;
                    $condition['school']=$one->school;
                    if($one->school!=session('S_USER_SCHOOL')&&session('S_MANAGE')!=1)
                    {
                        $errorRow++;
                        $info.=$one->reason.'非本学院条目不能修改。';
                        continue;
                    }
                    $data['audit']=$one->audit;
                    $data['credit']=$one->credit;
                    $data['reason']=$one->reason;
                    $data['cerdate']=$one->cerdate;
                    $updateRow += $this->query->table('creditapply')->where($condition)->update($data);
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['id'] = $one->id;
                    $condition['school']=$one->school;
                    $condition['audit'] = 0;
                    if($one->school!=session('S_USER_SCHOOL')&&session('S_MANAGE')!=1)
                    {
                        $errorRow++;
                        $info.=$one->reason.'非本学院条目不能修改。';
                        continue;
                    }
                    $deleteRow += $this->query->table('creditapply')->where($condition)->delete();
                }
            }
        }
        catch(\Exception $e){
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        if($updateRow>0) $info.=$updateRow.'条更新！</br>';
        if($deleteRow>0) $info.=$deleteRow.'条删除！</br>';
        if($insertRow>0) $info.=$insertRow.'条添加！</br>';
        $status=1;
        if($info=='') {
            $info="没有数据被更新";
            $status=0;
        }
        if($errorRow>0)
            $status=0;
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }

    function parseType($id,$type){
        $condition=null;

        $result="成功";
        $status=1;
        $condition['id']=$id;
        if(!MyAccess::checkCreditApplySchool($id)){
            $result="无法转换其他学院的条目";
            $status=0;
        }
        else{
            $data['type']=$type;
            $this->query->table('creditapply')->where($condition)->setField($data);
        }
        return array("info"=>$result,"status"=>$status);
    }
}