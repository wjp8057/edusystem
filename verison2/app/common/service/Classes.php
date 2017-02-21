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

/**班级信息
 * Class Classes
 * @package app\common\service
 */
class Classes extends MyService{
    /**获取班级列表
     * @param int $page
     * @param int $rows
     * @param string $classno 班号
     * @param string $classname 班名
     * @param string $school 学院
     * @return array|null
     */
    function getList($page=1,$rows=20,$classno='%',$classname='%',$school=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        if($classno!='%') $condition['classes.classno']=array('like',$classno);
        if($classname!='%') $condition['classes.classname']=array('like',$classname);
        if($school!='') $condition['classes.school']= $school;

        $subsql = Db::table('students')->group('classno')->field('classno,count(*) amount')->buildSql();

        $data=$this->query->table('classes')->join('schools ',' schools.school=classes.school')
            ->join('areas','areas.name=classes.area')
            ->join('classplan',' classplan.classno=classes.classno','LEFT')
            ->join('majorplan','majorplan.rowid=classplan.majorplanid','LEFT')
            ->join('majors','majors.majorschool=majorplan.majorschool','LEFT')
            ->join('majorcode ',' majorcode.code=majors.majorno','LEFT')
            ->join('majordirection ','majordirection.direction=majors.direction','LEFT')
            ->join($subsql.' t ',' t.classno=classes.classno','LEFT')
            ->page($page,$rows)
            ->field('rtrim(classes.classno) classno,rtrim(classes.classname) classname,schools.school,rtrim(schools.name) schoolname,
            rtrim(majors.majorno) major,rtrim(majorcode.name) as majorname,classes.students,isnull(t.amount,0) amount,
            classes.year,rtrim(majordirection.name) directionname,rtrim(majors.direction) direction,classes.area,areas.value areaname')
            ->where($condition)->order('classno')->select();
        $count= $this->query->table('classes')->where($condition)->count();
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
        $this->query->startTrans();
        try {
            if (isset($postData["inserted"])) {
                $updated = $postData["inserted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $data = null;
                    $data['classno'] = $one->classno;
                    $data['classname'] = $one->classname;
                    $data['school'] = $one->school;
                    $data['year'] = $one->year;
                    $data['area'] = $one->area;
                    $data['students'] = $one->students;
                    if ($data['school'] != session('S_USER_SCHOOL') && session('S_MANAGE') == 0) {
                        $info .= '无法为其它学院添加班级'.$one->classname .'</br>';
                        $status=0;
                        $errorRow++;
                    }
                    else {
                        $row = $this->query->table('classes')->insert($data);
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
                    $data = null;
                    $condition['classno'] = $one->classno;
                    $data['classname'] = $one->classname;
                    $data['school'] = $one->school;
                    $data['year'] = $one->year;
                    $data['area'] = $one->area;
                    $data['students'] = $one->students;
                    if(MyAccess::checkClassSchool($one->classno))
                        $updateRow += $this->query->table('classes')->where($condition)->update($data);
                    else{
                        $info.=$one->name.'不是本学院班级，无法更改信息</br>';
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
                    $condition['classno'] = $one->classno;
                    if(MyAccess::checkClassSchool($one->classno))
                        $deleteRow += $this->query->table('classes')->where($condition)->delete();
                    else{
                        $info.=$one->name.'不是本学院班级，无法删除</br>';
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

    public  function  addStudent($postData){
        $result=null;
        $count=0;
        $classno=$postData["classno"];
        if(MyAccess::checkClassSchool($classno)) {
            if (isset($postData["inserted"])) {
                $updated = $postData["inserted"];
                $listUpdated = json_decode($updated);
                $this->query->startTrans();
                try {
                    foreach ($listUpdated as $one) {
                        $data = null;
                        if(MyAccess::checkStudentSchool($one->studentno)) {
                            $data['classno'] = $classno;
                            $condition['studentno'] = $one->studentno;
                            $this->query->table('students')->where($condition)->update($data);
                            $count++;
                        }
                        else{
                            $result=array('info'=>'您无法修改其他学院学生的所在班级','status'=>0);
                            $this->query->rollback();
                            return $result;
                        }
                    }
                } catch (\Exception $e) {
                    $this->query->rollback();
                    throw $e;
                }
                $this->query->commit();
                $result=array('info'=>$count.'位学生添加成功','status'=>1);
            }
        }
        else{
            $result=array('info'=>'您无法修改其他班级的学生名单','status'=>0);
        }
        return $result;
    }

} 