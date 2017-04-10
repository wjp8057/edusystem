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
use app\common\access\MyException;
use app\common\access\MyService;
use think\Db;
use think\Exception;

/**教学计划课程
 * Class R12
 * @package app\common\service
 */
class MajorPlan extends MyService{

    /**读取
     * @param int $page
     * @param int $rows
     * @param string $year
     * @param string $school
     * @param string $id
     * @return array|null
     */
    function getList($page=1,$rows=20,$year='',$school='',$rowid=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        if($year!='') $condition['majorplan.year']=$year;
        if($school!='') $condition['majors.school']=$school;
        if($rowid!='')  $condition['majorplan.rowid']=$rowid;
        $data=$this->query->table('majorplan')
            ->join('majors','majors.majorschool=majorplan.majorschool')
            ->join('majorcode', 'majorcode.code=majors.majorno')
            ->join('majordirection','majordirection.direction=majors.direction')
            ->join('schools','schools.school=majors.school')
            ->page($page,$rows)
            ->field('years,year,rtrim(majorplan.rem) rem,majorplan.rowid ,mcredits,credits,rtrim(module) module,majorno,rtrim(majorcode.name) majorname,
            rtrim(majors.direction) direction,rtrim(majordirection.name) directionname,schools.school,rtrim(schools.name) schoolname,majors.majorschool')
            ->where($condition)->order('year,majorschool')->select();
        $count= $this->query->table('majorplan')  ->join('majors','majors.majorschool=majorplan.majorschool')->where($condition)->count();
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
                    $data['map'] = $one->map;
                    $data['year'] = $one->year;
                    $data['majorschool'] = $one->majorschool;
                    $data['module'] = $one->module;
                    $data['credits'] = $one->credits;
                    $data['mcredits'] = $one->mcredits;
                    $data['rem'] = $one->rem;
                    if(MyAccess::checkMajorSchool($one->majorschool)) {
                        $row=$this->query->table('majorplan')->insert($data);
                        if ($row > 0)
                            $insertRow++;
                    }
                    else{
                        $info.=$one->majorschool.'不是本学院专业，无法添加培养计划</br>';
                        $errorRow++;
                        $status=0;
                    }
                }
            }
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['rowid'] = $one->rowid;
                    $data['module'] = $one->module;
                    $data['year'] = $one->year;
                    $data['credits'] = $one->credits;
                    $data['mcredits'] = $one->mcredits;
                    $data['rem'] = $one->rem;
                    if(MyAccess::checkMajorSchool($one->majorschool)) {
                        $updateRow += $this->query->table('majorplan')->where($condition)->update($data);
                    }
                    else{
                        $info.=$one->majorschool.'不是本学院专业，无法修改培养计划</br>';
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
                    $condition['rowid'] = $one->rowid;
                    if(MyAccess::checkMajorSchool($one->majorschool)) {
                        $deleteRow += $this->query->table('majorplan')->where($condition)->delete();
                        $condition = null;
                        $condition['majorplan_rowid'] = $one->rowid;
                        $this->query->table('r30')->where($condition)->delete();
                    }
                    else{
                        $info.=$one->majorschool.'不是本学院专业，无法删除培养计划</br>';
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
    private  function getLastRowID($id,$alert=true){
        $condition['id']=$id;
        $result=Db::table('majorplan')
            ->field('rowid')->where($condition)->select();
        if(!is_array($result)||count($result)!=1) {
            if($alert)
                throw new Exception('id' . $id, MyException::ITEM_NOT_EXISTS);
        }
        return $result[0]['rowid'];
    }
    public function copy($rowid,$module,$rem=''){
        //检查输入有效性
        $result=null;
        if(MyAccess::checkMajorPlanSchool($rowid)) {
            $condition=null;
            $condition['rowid']=$rowid;
            $this->query->table('majorplan')
                ->where($condition)
                ->field("'".$module."','".$rem."',year,map,mcredits,credits,majorschool")
                ->selectInsert('module,rem,year,map,mcredits,credits,majorschool', 'majorplan');
            $id=$this->query->getLastInsID();
            $new_rowid=self::getLastRowID($id);
            if($new_rowid) {
                $condition = null;
                $condition['majorplan_rowid'] = $rowid;
                $this->query->table('r30')
                    ->where($condition)
                    ->field("'" . $new_rowid . "',progno,credits,mcredits,form")
                    ->selectInsert('majorplan_rowid,progno,credits,mcredits,form', 'r30');

                $result = ['status' => 1, 'info' => '复制完成！'];
            }
            else
                $result = ['status' => 1, 'info' => '培养计划复制出错！'];
        }
        else
            $result = ['status' => 0, 'info' => '您无法复制其他学院的培养方案'];

        return $result;
    }

} 