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
        $errorRow=0;
        $info="";
        $status=1;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["inserted"])) {
                $updated = $postData["inserted"];
                $listUpdated = json_decode($updated);
                $data = null;
                foreach ($listUpdated as $one) {
                    $data['majorno'] = $one->majorno;
                    $data['years'] = $one->years;
                    $data['branch'] = $one->branch;
                    $data['school'] = $one->school;
                    $data['degree'] = $one->degree;
                    $data['rem'] = $one->rem;
                    $data['direction'] = $one->direction;
                    $data['majorschool'] = $one->majorschool;
                    if ($data['school'] != session('S_USER_SCHOOL') && session('S_MANAGE') == 0) {
                        $info .= '无法为其它学院添加专业'.$one->majorschool .'</br>';
                        $status=0;
                        $errorRow++;
                    }
                    else {
                        $row = $this->query->table('majors')->insert($data);
                        if ($row > 0)
                            $insertRow++;
                    }
                }
            }
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['majorschool'] = $one->majorschool;
                    $data['majorno'] = $one->majorno;
                    $data['years'] = $one->years;
                    $data['branch'] = $one->branch;
                    $data['school'] = $one->school;
                    $data['degree'] = $one->degree;
                    $data['rem'] = $one->rem;
                    $data['direction'] = $one->direction;
                    if(MyAccess::checkMajorSchool($one->majorschool))
                        $updateRow += $this->query->table('majors')->where($condition)->update($data);
                    else{
                        $info.=$one->majorschool.'无法修改其他学院专业</br>';
                        $errorRow++;
                        $status=0;
                    }
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['majorschool'] = $one->majorschool;
                    if(MyAccess::checkMajorSchool($one->majorschool))
                        $deleteRow += $this->query->table('majors')->where($condition)->delete();
                    else{
                        $info.=$one->majorschool.'不是本学院专业，无法删除</br>';
                        $errorRow++;
                        $status=0;
                    }

                }
            }
        }
        catch(\Exception $e){
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        if($updateRow+$deleteRow+$insertRow+$errorRow==0){
            $status=0;
            $info="没有数据更新";
        }
        else {
            if ($updateRow > 0) $info .= $updateRow . '条更新！</br>';
            if ($deleteRow > 0) $info .= $deleteRow . '条删除！</br>';
            if ($insertRow > 0) $info .= $insertRow . '条添加！</br>';
        }
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }

} 