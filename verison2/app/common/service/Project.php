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
// | Created:2016/11/21 15:10
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyService;

class Project extends  MyService{
    public function  getList($page=1, $rows=20,$year,$term,$type='',$school=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['year']=$year;
        $condition['term']=$term;
        if($type!='')   $condition['project.type']=$type;
        if($school!='') $condition['project.school']=$school;

        $data= $this->query->table('project')
            ->join('schools','schools.school=project.school')
            ->join('credittype','credittype.type=project.type')
            ->field('project.id,rtrim(project.name) name,year,term,credit,project.type,(credittype.name) typename,project.date,project.amount,
            schools.school,rtrim(schools.name) schoolname')
            ->where($condition)->page($page,$rows)->order('id')->select();
        $count= $this->query->table('project')->where($condition)->count();
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
                    $data['year'] = $one->year;
                    $data['term'] = $one->term;
                    $data['name'] = $one->name;
                    $data['credit'] = $one->credit;
                    $data['type'] = $one->type;
                    $data['date'] = $one->date;
                    $data['school'] = $one->school;
                    $row = $this->query->table('project')->insert($data);
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
                    $data['name'] = $one->name;
                    $data['credit'] = $one->credit;
                    $data['type'] = $one->type;
                    $data['date'] = $one->date;
                    $updateRow += $this->query->table('project')->where($condition)->update($data);
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['id'] = $one->id;
                    $condition['amount']=$one->amount;
                    $deleteRow += $this->query->table('project')->where($condition)->delete();
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