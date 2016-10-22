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


use app\common\access\MyService;

/**校内开设专业
 * Class Major
 * @package app\common\service
 */
class Major extends MyService{

    /**获取开设专业列表
     * @param int $page
     * @param int $rows
     * @param string $years
     * @param string $school
     * @param string $majorname
     * @return array|null
     */
    function getList($page=1,$rows=20,$years='',$school='',$majorname='%'){
        $result=null;
        $condition=null;
        if($years!='') $condition['majors.years']=$years;
        if($school!='') $condition['majors.school']=$school;
        if($majorname!='%') $condition['majorcode.name']=array('like',$majorname);
        $data=$this->query->table('majors')->page($page,$rows)
            ->field('majorschool,majorno,rtrim(majorcode.name) majorname,rtrim(majors.direction) direction ,rtrim(majordirection.name) directionname,rtrim(rem) rem,
            majors.degree ,rtrim(degreeoptions.name) degreename,rtrim(majors.branch) branch,rtrim(branchcode.name) branchname,majors.school,rtrim(schools.name) schoolname,majors.years')
            ->join('branchcode','branchcode.code=majors.branch')
            ->join('degreeoptions','degreeoptions.code=majors.degree')
            ->join('schools','schools.school=majors.school')
            ->join('majorcode','majorcode.code=majors.majorno')
            ->join('majordirection','majordirection.direction=majors.direction')
            ->where($condition)
            ->order('school,majorschool')->select();
        $count= $this->query->table('majors')
            ->join('majorcode','majorcode.code=majors.majorno')
            ->where($condition)->count();
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
                    $data['code'] = $one->code;
                    $data['name'] = $one->name;
                    $data['englishname'] = $one->englishname;
                    $row = $this->query->table('majorcode')->insert($data);
                    if ($row > 0)
                        $insertRow++;
                }
            }
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['code'] = $one->code;
                    $data['name'] = $one->name;
                    $data['englishname'] = $one->englishname;
                    $updateRow += $this->query->table('majorcode')->where($condition)->update($data);
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['code'] = $one->code;
                    $deleteRow += $this->query->table('majorcode')->where($condition)->delete();
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