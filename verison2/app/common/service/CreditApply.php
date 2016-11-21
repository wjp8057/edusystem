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


use app\common\access\MyService;

class CreditApply extends MyService {
    function getList($page=1,$rows=20,$year='',$term='',$studentno='%',$classno='%',$school='',$type=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        if($year!='')  $condition['creditapply.year']=$year;
        if($term!='')  $condition['term']=$term;
        if($school!='') $condition['classes.school']=$school;
        if($classno!='%')$condition['students.classno']=array('like',$classno);
        if($studentno!='%')$condition['creditapply.studentno']=array('like',$studentno);
        if($type!='') $condition['creditapply.type']=$type;
        $data=$this->query->table('creditapply')->page($page,$rows)
            ->join('students','students.studentno=creditapply.studentno')
            ->join('classes','students.classno=classes.classno')
            ->join('schools','schools.school=classes.school')
            ->join('credittype','credittype.type=creditapply.type')
            ->where($condition)
            ->field('creditapply.id,creditapply.year,creditapply.term,students.studentno,rtrim(reason) reason,credit,cerdate,applydate,rtrim(schools.name) schoolname,
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
}