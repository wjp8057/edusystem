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

/**学生绑定的培养方案
 * Class Studentplan
 * @package app\common\service
 */
class Studentplan extends MyService{


    /**读取
     * @param int $page
     * @param int $rows
     * @return array|null
     */
    function getList($page=1,$rows=20,$majorplanid){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['majorplanid']=$majorplanid;
        $data=$this->query->table('studentplan')
            ->join('students','students.studentno=studentplan.studentno')
            ->join('classes','classes.classno=students.classno')
            ->join('schools','schools.school=classes.school')
            ->page($page,$rows)
            ->field("students.studentno,rtrim(students.name) name,rtrim(classes.classname) classname,schools.school,rtrim(schools.name) schoolname,'1' as old")->order('studentno')
            ->where($condition)->select();
        $count= $this->query->table('studentplan')->where($condition)->count();
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
                $data = null;
                foreach ($listUpdated as $one) {
                   if(MyAccess::checkStudentSchool($one->studentno))
                   {
                       $condition=null;
                       $condition['studentno']=$one->studentno;
                       $condition['type']='M';//默认删除主修计划 todo:以后更新
                       $this->query->table('studentplan')->where($condition)->delete();
                       $data['studentno']=$one->studentno;
                       $data['majorplanid']=$majorplanid;
                       $data['type']='M';
                       $row = $this->query->table('studentplan')->insert($data);
                       if ($row > 0)
                           $insertRow++;
                   }
                    else{
                        $info.=$one->name.'不是本学院学生，无法修改其培养计划</br>';
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
        if($insertRow+$errorRow==0){
            $status=0;
            $info="没有数据更新";
        }
        else{
            $info.=$insertRow."条更新";
        }
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }


    function bindByClassNo($classno){
        $effectRow=0;
        $this->query->startTrans();
        try {
            $condition=null;
            $condition['students.classno']=$classno;
            $this->query->table('studentplan')
                ->join('students','students.studentno=studentplan.studentno')->where($condition)->delete();
            $effectRow=$this->query->table('classplan')
                ->join('students','students.classno=classplan.classno')
                ->where($condition)->field("students.studentno,classplan.majorplanid,'M'")->selectInsert('studentno,majorplanid,type','studentplan');
        }
        catch(\Exception $e){
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();

        return $effectRow;
    }
} 