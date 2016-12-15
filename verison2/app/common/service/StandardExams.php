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
// | Created:2016/12/15 13:54
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyService;

class StandardExams extends MyService {

    function getList($page=1,$rows=20,$examname='%'){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        if($examname!='%')  $condition['examname']=array('like',$examname);
        $data=$this->query->table('standardexams')
            ->join('testlevel','testlevel.name=standardexams.testlevel')
            ->page($page,$rows)
            ->field('rtrim(courseno) courseno,rtrim(examname) examname,testlevel,rtrim(testlevel.value) testlevelname,rtrim(schoolcode) schoolcode,
            rtrim(testcode) testcode,rtrim(rem) rem,recno')
            ->where($condition)
            ->order('examname')->select();
        $count= $this->query->table('standardexams')->where($condition)->count();
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
                    $data['courseno'] = $one->courseno;
                    $data['examname'] = $one->examname;
                    $data['testlevel'] = $one->testlevel;
                    $data['testcode'] = $one->testcode;
                    $data['schoolcode'] = $one->schoolcode;
                    $data['rem'] = $one->rem;
                    $row = $this->query->table('standardexams')->insert($data);
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
                    $data['courseno'] = $one->courseno;
                    $data['examname'] = $one->examname;
                    $data['testlevel'] = $one->testlevel;
                    $data['testcode'] = $one->testcode;
                    $data['schoolcode'] = $one->schoolcode;
                    $data['rem'] = $one->rem;
                    $updateRow += $this->query->table('standardexams')->where($condition)->update($data);
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['recno'] = $one->recno;
                    $deleteRow += $this->query->table('standardexams')->where($condition)->delete();
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