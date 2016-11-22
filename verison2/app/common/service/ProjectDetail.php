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
// | Created:2016/11/22 8:24
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyService;
//学分认定详细名单
class ProjectDetail extends MyService {
    //读取
    public function  getList($page=1, $rows=20,$type='',$map='',$year='',$term='',$reason='%',$studentno='%',$school='',$verify=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        if($year!='')  $condition['project.year']=$year;
        if($term!='')  $condition['project.term']=$term;
        if($type!='')  $condition['project.type']=$type;
        if($map!='')   $condition['projectdetail.map']=$map;
        if($reason!='%')   $condition['rtrim(project.name)+projectdetail.reason']=array('like',$reason);
        if($studentno!='%')   $condition['projectdetail.studentno']=array('like',$reason);
        if($school!='')   $condition['project.school']=$school;
        if($verify!='')   $condition['projectdetail.verify']=$verify;
        $data= $this->query->table('projectdetail')
            ->join('project','project.id=projectdetail.map')
            ->join('schools','schools.school=project.school')
            ->join('credittype','credittype.type=project.type')
            ->join('students','students.studentno=projectdetail.studentno')
            ->join('classes','classes.classno=students.classno')
            ->field('projectdetail.id,rtrim(project.name)+rtrim(projectdetail.reason) reason,project.year,term,projectdetail.credit,project.type,(credittype.name) typename,projectdetail.cerdate,
            schools.school,rtrim(schools.name) schoolname,rtrim(classes.classname)classname,projectdetail.studentno,projectdetail.date applydate,rtrim(students.name) studentname,projectdetail.verify,
            rtrim(projectdetail.reason) name,1 as audit ')
            ->where($condition)->page($page,$rows)->order('id')->select();
        $count= $this->query->table('projectdetail')
            ->join('project','project.id=projectdetail.map')
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
            $map=$postData["map"];
            if (isset($postData["inserted"])) {
                $updated = $postData["inserted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $data = null;
                    $data['map'] = $map;
                    $data['studentno'] = $one->studentno;
                    $data['reason'] = $one->reason;
                    $data['credit'] = $one->credit;
                    $data['cerdate'] = $one->cerdate;
                    $row = $this->query->table('projectdetail')->insert($data);
                    if ($row > 0)
                        $insertRow++;
                }
            }
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $data = null;
                    $condition['id'] = $one->id;
                    $condition['verify'] = 0;
                    $data['reason'] = $one->reason;
                    $data['credit'] = $one->credit;
                    $data['cerdate'] = $one->cerdate;
                    $updateRow += $this->query->table('projectdetail')->where($condition)->update($data);
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['id'] = $one->id;
                    $condition['map'] = $map;
                    $condition['verify'] = 0;
                    $deleteRow += $this->query->table('projectdetail')->where($condition)->delete();
                }
            }
            //更新人数
        }
        catch(\Exception $e){
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        Project::updateAmount($map);
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

    //认定
    function verify($postData){
        $updateRow=0;
        $deleteRow=0;
        $insertRow=0;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $data = null;
                    $condition['id'] = $one->id;
                    $data['verify'] = $one->verify;
                    $data['credit'] = $one->credit;
                    $data['cerdate'] = $one->cerdate;
                    $updateRow += $this->query->table('projectdetail')->where($condition)->update($data);
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['id'] = $one->id;
                    $deleteRow += $this->query->table('projectdetail')->where($condition)->delete();
                }
            }
            //更新人数
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