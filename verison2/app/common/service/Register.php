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

class Register extends MyService {

    /**获取学生学籍异动信息
     * @param int $page
     * @param int $rows
     * @param string $year
     * @param string $term
     * @param string $studentno
     * @param string $name
     * @param string $infotype
     * @return array|null
     * @throws \think\Exception
     */
    function getList($page=1,$rows=20,$year='',$term='',$studentno='%',$name='%',$infotype=''){
        $result=null;
        $condition=null;
        if($year==''||$term=='')
            throw new \think\Exception('year or term is empty ', MyException::PARAM_NOT_CORRECT);
        $condition['registries.year']=$year;
        $condition['registries.term']=$term;
        if($studentno!='%') $condition['students.studentno']=array('like',$studentno);
        if($name!='%') $condition['students.name']=array('like',$name);
        if($infotype!='') $condition['registries.infotype']= $infotype;
        $data=$this->query->table('registries')->join('students ',' students.studentno=registries.studentno')
            ->join('classes  ',' classes.classno=students.classno')
            ->join('schools  ',' schools.school=classes.school')
            ->join('sexcode  ',' sexcode.code=students.sex')
            ->join('infotype  ',' infotype.code=registries.infotype')
            ->page($page,$rows)
            ->field('students.studentno,rtrim(students.name) name,rtrim(sexcode.name) sexname,
            rtrim(classes.classname) as classname,rtrim(schools.name) schoolname,schools.school,
            rtrim(registries.infotype) as infotype,rtrim(infotype.name) as infotypename,
            rtrim(registries.fileno) fileno,registries.date,registries.recno,rtrim(rem) rem,registries.year,registries.term')
            ->where($condition)->order('date desc')->select();
        $count= $this->query->table('registries')->join('students ',' students.studentno=registries.studentno')
            ->join('classes ',' classes.classno=students.classno')->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

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
                    $yearterm=get_year_term();
                    $data['year']=$yearterm['year'];
                    $data['term']=$yearterm['term'];
                    $data['studentno'] = $one->studentno;
                    $data['infotype'] = $one->infotype;
                    $data['date'] = $one->date;
                    $data['fileno'] = $one->fileno;
                    $data['rem'] = $one->rem;
                    if(MyAccess::checkStudentSchool($one->studentno)) {
                        $this->query->table('registries')->insert($data);
                        $insertRow++;
                    }
                    else{
                        $info.=$one->studentno.'不是本学院学生，无法添加</br>';
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
                    $data = null;
                    $condition['recno'] = $one->recno;
                    $data['studentno'] = $one->studentno;
                    $data['infotype'] = $one->infotype;
                    $data['date'] = $one->date;
                    $data['fileno'] = $one->fileno;
                    $data['rem'] = $one->rem;
                    if(MyAccess::checkStudentSchool($one->studentno))
                        $updateRow += $this->query->table('registries')->where($condition)->update($data);
                    else{
                        $info.=$one->name.'不是本学院学生，无法更改信息</br>';
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
                    $condition['recno'] = $one->recno;
                    if(MyAccess::checkStudentSchool($one->studentno)) {
                        $deleteRow += $this->query->table('registries')->where($condition)->delete();
                    }
                    else{
                        $info.=$one->name.'不是本学院学生，无法删除</br>';
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