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
use think\Exception;

class Regdata extends MyService{
    /**
     * @param int $page 页码
     * @param int $rows 每页条数
     * @param string $year 学年
     * @param string $term 学期
     * @param string $studentno 学号
     * @param string $classno 班号
     * @param string $school 学院
     * @param int $type 注册类型（0全部，1已注册，2未注册）
     * @return array|null
     * @throws \think\Exception
     */
    function getList($page=1,$rows=20,$year='2015',$term='1',$studentno='%',$classno='%',$school='',$type=0){
        if($year==''||$term=='')
            throw new Exception('year or term is empty ',MyException::PARAM_NOT_CORRECT);

        $result=null;
        $condition=null;
        $condition['students.classno']=array('like',$classno);
        $condition['students.studentno']=array('like',$studentno);
        //全部信息
        $str="(regdata.year is null or regdata.year=:year) and (regdata.term is null or regdata.term=:term) and students.status in ('A','C','H')";
        if($type==1)
            $str="(regdata.year is null or regdata.year=:year) and (regdata.term is null or regdata.term=:term) and students.status in ('A','C','H')
            and regdata.regcode is not null";
        else if($type==2)
            $str="(regdata.year is null) and (regdata.term is null) and students.status in ('A','C','H')";

        if($school!='') $condition['classes.school']=$school;;
        $data=$this->query->table('regdata')->join('students ',' students.studentno=regdata.studentno','RIGHT')
            ->join('classes ',' classes.classno=students.classno')
            ->join('schools ',' schools.school=classes.school')
            ->join('statusoptions ',' statusoptions.name=students.status')
            ->join('regcodeoptions ',' regcodeoptions.name=regdata.regcode')
            ->page($page,$rows)->field("students.name,students.studentno,rtrim(classes.classname) classname,
            rtrim(schools.name) schoolname,rtrim(statusoptions.value) statusname,regdata.regcode,isnull(rtrim(regcodeoptions.value),'未注册') regname,regdata.regdate")
            ->where($str)->bind(['year'=>$year,'term'=>$term])->where($condition)
            ->select();
        $count= $this->query->table('regdata')->join('students ',' students.studentno=regdata.studentno','RIGHT')
            ->join('classes ',' classes.classno=students.classno')
            ->join('schools ',' schools.school=classes.school')
            ->where($str)->bind(['year'=>$year,'term'=>$term])->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

    function update($postData){
        $year=$postData["year"];
        $term=$postData["term"];
        $regcode=$postData["regcode"];
        if($year==''||$term==''||$regcode=='')
            throw new Exception('year term regcode is empty',MyException::PARAM_NOT_CORRECT);
        $updateRow=0;
        $info='';
        $status=1;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["updated"])) {

                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {

                    //在管辖范围
                    $studentno=$one->studentno;
                    if(MyAccess::checkStudentSchool($studentno)){
                        $condition = null;
                        $condition['studentno']=$studentno;
                        $condition['year']=$year;
                        $condition['term']=$term;
                        $this->query->table('regdata')->where($condition)->delete();
                        $data=null;
                        $data['year']=$year;
                        $data['term']=$term;
                        $data['studentno']=$studentno;
                        $data['regcode']=$regcode;
                        $data['regdate'] = date('Y-m-d H:i:s');
                        $updateRow+=$this->query->table('regdata')->insert($data);
                    }
                }
            }
        }
        catch(\Exception $e){
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        if($updateRow>0) $info.=$updateRow.'条更新！</br>';
        if($info=='') {
            $info="没有数据被更新";
            $status=0;
        }
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }

} 