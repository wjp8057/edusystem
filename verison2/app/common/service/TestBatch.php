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
// | Created:2016/11/28 10:31
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyService;

class TestBatch extends  MyService{
    //读取
    function getList($page=1,$rows=20,$year,$term,$type){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['year']=$year;
        $condition['term']=$term;
        $condition['type']=$type;
        $data=$this->query->table('testbatch')->page($page,$rows)
            ->field('recno,year,term,flag,testtime,amount,type')->where($condition)->order('flag')->select();
        $count= $this->query->table('testbatch')->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
    //更新
    function update($postData){
        $updateRow=0;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['recno'] = $one->recno;
                    $data['testtime'] = $one->testtime;
                    $updateRow += $this->query->table('testbatch')->where($condition)->update($data);
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
        $status=1;
        if($info=='') {
            $info="没有数据被更新";
            $status=0;
        }
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }
}