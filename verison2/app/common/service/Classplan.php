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

/**班级绑定的培养方案
 * Class Classplan
 * @package app\common\service
 */
class Classplan extends MyService{

    /**获取绑定了指定教学计划的班级
     * @param int $page
     * @param int $rows
     * @param string $classno
     * @param string $classname
     * @param string $school
     * @return array|null
     */
    function getList($page=1,$rows=50,$majorplanid){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['majorplanid']= $majorplanid;
        $data=$this->query->table('classplan')
            ->join('classes','classes.classno=classplan.classno')
            ->join('schools ',' schools.school=classes.school')
            ->page($page,$rows)
            ->field('rtrim(classes.classno) classno,rtrim(classes.classname) classname,schools.school,rtrim(schools.name) schoolname')
            ->where($condition)->order('classno')->select();
        $count= $this->query->table('classplan')->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

    /**更新班级信息
     * @param $postData
     * @return array
     * @throws \Exception
     */
    public function  update($postData){
        $updateRow=0;
        $deleteRow=0;
        $insertRow=0;
        $errorRow=0;
        $info="";
        $status=1;
        //更新部分
        //开始事务
        $majorplanid=$postData["majorplanid"];
        $this->query->startTrans();
        try {
            if (isset($postData["inserted"])) {
                $updated = $postData["inserted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    if (!MyAccess::checkClassSchool($one->classno)) {
                        $info .= '无法为其它学院的班级绑定培养计划'.$one->classname .'</br>';
                        $status=0;
                        $errorRow++;
                    }
                    else {
                        $data = null;
                        $condition=null;
                        $condition['classno']=$one->classno;
                        $this->query->table('classplan')->delete($condition);
                        $data['classno'] = $one->classno;
                        $data['majorplanid'] = $majorplanid;
                        $row = $this->query->table('classplan')->insert($data);
                        if ($row > 0)
                            $insertRow++;
                    }
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    if(MyAccess::checkClassSchool($one->classno)) {
                        $condition = null;
                        $condition['classno'] = $one->classno;
                        $condition['majorplanid']=$majorplanid;
                        $deleteRow += $this->query->table('classplan')->where($condition)->delete();
                    }
                    else{
                        $info.=$one->name.'无法删除其他学院班级的培养方案</br>';
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