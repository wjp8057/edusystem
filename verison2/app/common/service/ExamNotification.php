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
// | Created:2016/12/15 16:10
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyService;

class ExamNotification extends  MyService{

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
                    $data['dateofexam'] = $one->examdate;
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
                    $data['map'] = $one->map;
                    $data['year'] = $one->year;
                    $data['term'] = $one->term;
                    $data['fee'] = $one->fee;
                    $data['deadline'] = $one->deadline;
                    $data['dateofexam'] = $one->examdate;
                    $data['rem'] = $one->rem;
                    $data['lock'] = $one->lock;
                    $data['notified'] = $one->notified;
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