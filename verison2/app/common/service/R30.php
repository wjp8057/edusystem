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

namespace app\common\service;


use app\common\access\MyAccess;
use app\common\access\MyService;

/**培养计划的教学计划绑定
 * Class R30
 * @package app\common\service
 */
class R30 extends MyService{

    /**读取
     * @param int $page
     * @param int $rows
     * @return array|null
     */
    function getList($page=1,$rows=20,$majorplanid){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['majorplan_rowid']=$majorplanid;
        $data=$this->query->table('r30')
            ->join('programs','programs.programno=r30.progno')
            ->join('programform','programform.name=r30.form')
            ->page($page,$rows)
            ->field('progno programno,rtrim(programs.progname)progname,credits,mcredits,r30.form,rtrim(programform.value) formname')
            ->where($condition)->order('programno')->select();
        $count= $this->query->table('r30')->where($condition)->count();
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
        $majorplanid=$postData["majorplanid"];
        if(!MyAccess::checkMajorPlanSchool($majorplanid))
        {
            return array('info'=>'无法为其他学院的培养方案更新教学计划','status'=>0);
        }
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["inserted"])) {
                $updated = $postData["inserted"];
                $listUpdated = json_decode($updated);
                $data = null;
                foreach ($listUpdated as $one) {
                    $data['majorplan_rowid'] = $majorplanid;
                    $data['progno'] = $one->programno;
                    $data['form'] = $one->form;
                    $data['credits'] = $one->credits;
                    $data['mcredits'] = $one->mcredits;
                    $row = $this->query->table('r30')->insert($data);
                    if ($row > 0)
                        $insertRow++;
                }
            }
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['majorplan_rowid'] = $majorplanid;
                    $condition['progno'] = $one->programno;
                    $data['form'] = $one->form;
                    $data['credits'] = $one->credits;
                    $data['mcredits'] = $one->mcredits;
                    $updateRow += $this->query->table('r30')->where($condition)->update($data);
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['majorplan_rowid'] = $majorplanid;
                    $condition['progno'] = $one->programno;
                    $deleteRow += $this->query->table('r30')->where($condition)->delete();
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