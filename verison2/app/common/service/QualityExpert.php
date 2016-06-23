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
// | Created:2016/6/22 10:10
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyException;
use app\common\access\MyService;
use think\Db;
use think\Exception;

class QualityExpert extends MyService{
    /**获取某督导的具体打分情况
     * @param int $page
     * @param int $rows
     * @param $year
     * @param $term
     * @param string $expert
     * @param string $teacherno
     * @param string $name
     * @param string $school
     * @return array|null
     */
    public function getList($page = 1, $rows = 20,$year,$term,$expert='%', $teacherno='%',$name='%',$school='')
    {
        $result=null;
        $condition['year'] = $year;
        $condition['term'] = $term;
        if($expert!='%') $condition['qualityexpert.expert'] = array('like',$expert);
        if($teacherno!='%') $condition['qualityexpert.teacherno'] = array('like',$expert);
        if($name!='%') $condition['expert.name'] = array('like',$expert);
        if($school!='') $condition['expert.school'] = $school;

        $data = $this->query->table('qualityexpert')
            ->join('teachers expert', 'expert.teacherno=qualityexpert.expert')
            ->join('teachers', 'teachers.teacherno=qualityexpert.teacherno')
            ->join('schools ', ' schools.school=teachers.school')
            ->join('teachertype ', ' teachertype.name=teachers.type')
            ->page($page, $rows)
            ->field('qualityexpert.id,rtrim(expert.name) as expertname,teachers.teacherno,rtrim(teachers.name) teachername,schools.school,(schools.name) as schoolname,
            rtrim(teachertype.value) as typename,score,normalscore,qualityexpert.rem')
            ->where($condition)->order('id')->select();
        $count = $this->query->table('qualityexpert')->where($condition)->count();
        if (is_array($data) && count($data) > 0)
            $result = array('total' => $count, 'rows' => $data);
        return $result;
    }

    /**更新打分
     * @param $postData
     * @return array
     * @throws \Exception
     */
    public function update($postData){
        $updateRow = 0;
        $deleteRow = 0;
        $insertRow = 0;
        $errorRow = 0;
        $info = "";
        $status = 1;
        //更新部分
        //开始事务
        $expert=$postData['expert'];
        $year=$postData['year'];
        $term=$postData['term'];
        $this->query->startTrans();
        try {
            if (isset($postData["inserted"])) {
                $updated = $postData["inserted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $data = null;
                    $data['expert']=$expert;
                    $data['year']=$year;
                    $data['term']=$term;
                    $data['teacherno'] = $one->teacherno;
                    $data['score'] = $one->score;
                    $data['normalscore'] = $one->normalscore;
                    $data['rem'] = $one->rem;
                    $row = $this->query->table('qualityexpert')->insert($data);
                    if ($row > 0)
                        $insertRow++;
                }
            }
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $data = null;
                    $condition['id'] = $one->id;
                    $data['score'] = $one->score;
                    $data['normalscore'] = $one->normalscore;
                    $data['rem'] = $one->rem;
                    $updateRow += $this->query->table('qualityexpert')->where($condition)->update($data);
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['id'] = $one->id;
                    $this->query->table('qualityexpert')->where($condition)->delete();
                    $deleteRow++;
                }
            }
        } catch (\Exception $e) {
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        if ($updateRow + $deleteRow + $insertRow + $errorRow == 0) {
            $status = 0;
            $info = "没有数据更新";
        } else {
            if ($updateRow > 0) $info .= $updateRow . '条更新！</br>';
            if ($deleteRow > 0) $info .= $deleteRow . '条删除！</br>';
            if ($insertRow > 0) $info .= $insertRow . '条添加！</br>';
        }
        $result = array('info' => $info, 'status' => $status);
        return $result;
    }

    /**计算归一分
     * @param $year
     * @param $term
     * @param string $expert
     * @param $standard
     * @return array
     * @throws Exception
     */
    public function  calculate($year,$term,$expert='%',$standard){
        if(!is_numeric($standard))
            throw new Exception('standard:' .$standard.' is not number',MyException::PARAM_NOT_CORRECT);
        $condition=null;
        $condition['qualityexpert.year']=$year;
        $condition['qualityexpert.term']=$term;
        if($expert!='%')$condition['qualityexpert.expert']=array('like',$expert);

        $subsql = Db::table('qualityexpert')
            ->where($condition)->field('expert,cast(avg(score*1.0)  as decimal(8,2) ) avgscore')->group('expert')->buildSql();

        $condition=null;
        $condition['year']=$year;
        $condition['term']=$term;
        $data['normalscore']=array('exp','case when  score*'.$standard.'/avgscore>95 then 95 else score*'.$standard.'/avgscore end');
        if($expert!='%')$condition['qualityexpert.expert']=array('like',$expert);
        $this->query->table('qualityexpert')->join($subsql.' t','t.expert=qualityexpert.expert')->where($condition)
            ->update($data);
        return ['info' => '计算完成', 'status' => "1"];
    }

    /**获取督导打分情况汇总
     * @param int $page
     * @param int $rows
     * @param $year
     * @param $term
     * @param string $expert
     * @param string $name
     * @param string $school
     * @return array|null
     */
    public function getScoreSummary($page = 1, $rows = 20,$year,$term, $expert='%',$name='%',$school='')
    {
        $result=null;
        $condition=null;
        $condition['qualityexpert.year']=$year;
        $condition['qualityexpert.term']=$term;
        if($expert!='%')$condition['qualityexpert.expert']=array('like',$expert);
        $subsql = Db::table('qualityexpert')
            ->where($condition)->field('expert,count(*) as amount')->group('expert')->buildSql();
        $condition=null;
        $result=null;
        if ($expert != '%') $condition['teachers.teacherno'] = array('like', $expert);
        if ($name != '%') $condition['teachers.name'] = array('like', $name);
        if ($school != '') $condition['teachers.school'] = $school;
        $data = $this->query->table('teachers')->join('teachertype ', ' teachertype.name=teachers.type')
            ->join('teacherjob ', ' teacherjob.job=teachers.job','LEFT')
            ->join('positions ', ' positions.name=teachers.position')
            ->join('schools ', ' schools.school=teachers.school')
            ->join('sexcode ', ' sexcode.code=teachers.sex')
            ->join($subsql.' t','t.expert=teachers.teacherno')
            ->page($page, $rows)
            ->field('rtrim(teachers.name) name,teachers.teacherno,teachers.position,rtrim(positions.value) positionname,teachers.type,rtrim(teachertype.value) typename,
            rtrim(teacherjob.name) jobname,teachers.job,rtrim(schools.name) schoolname,teachers.school,teachers.sex,rtrim(sexcode.name) sexname,teachers.rem,t.amount')
            ->where($condition)->order('teacherno')->select();
        $count = $this->query->table('teachers')
            ->join($subsql.' t','t.expert=teachers.teacherno')->where($condition)->count();

        if (is_array($data) && count($data) > 0)
            $result = array('total' => $count, 'rows' => $data);
        return $result;
    }

    /**教师学年平均分
     * @param int $page
     * @param int $rows
     * @param $year
     * @param string $teacherno
     * @param string $name
     * @param string $school
     * @return array|null
     */
    public function getTeacherScore($page = 1, $rows = 20,$year, $teacherno='%',$name='%',$school='')
    {
        $result=null;
        $condition=null;
        $condition['qualityexpert.year']=$year;
        if($teacherno!='%')$condition['qualityexpert.teacherno']=array('like',$teacherno);
        $subsql = Db::table('qualityexpert')
            ->where($condition)->field('teacherno,count(*) as amount,cast(avg(normalscore) as decimal(8,2)) score')->group('teacherno')->buildSql();
        $condition=null;
        $result=null;
        if ($teacherno != '%') $condition['teachers.teacherno'] = array('like', $teacherno);
        if ($name != '%') $condition['teachers.name'] = array('like', $name);
        if ($school != '') $condition['teachers.school'] = $school;
        $data = $this->query->table('teachers')->join('teachertype ', ' teachertype.name=teachers.type')
            ->join('teacherjob ', ' teacherjob.job=teachers.job','LEFT')
            ->join('positions ', ' positions.name=teachers.position')
            ->join('schools ', ' schools.school=teachers.school')
            ->join('sexcode ', ' sexcode.code=teachers.sex')
            ->join($subsql.' t','t.teacherno=teachers.teacherno')
            ->page($page, $rows)
            ->field('rtrim(teachers.name) teachername,teachers.teacherno,teachers.position,rtrim(positions.value) positionname,teachers.type,rtrim(teachertype.value) typename,
            rtrim(teacherjob.name) jobname,teachers.job,rtrim(schools.name) schoolname,teachers.school,teachers.sex,rtrim(sexcode.name) sexname,teachers.rem,t.amount,t.score')
            ->where($condition)->order('teacherno')->select();
        $count = $this->query->table('teachers')
            ->join($subsql.' t','t.teacherno=teachers.teacherno')->where($condition)->count();

        if (is_array($data) && count($data) > 0)
            $result = array('total' => $count, 'rows' => $data);
        return $result;
    }

    /**获取某位教师一学年的督导打分情况
     * @param int $page
     * @param int $rows
     * @param $year
     * @param $teacherno
     * @return array|null
     */
    public function getTeacherExpert($page = 1, $rows = 20,$year,$teacherno)
    {
        $result=null;
        $condition=null;
        $condition['year']=$year;
        $condition['qualityexpert.teacherno']=$teacherno;
        $data = $this->query->table('qualityexpert')
            ->join('teachers','teachers.teacherno=qualityexpert.expert')
            ->join('positions ', ' positions.name=teachers.position')
            ->join('schools ', ' schools.school=teachers.school')
            ->join('sexcode ', ' sexcode.code=teachers.sex')
            ->page($page, $rows)
            ->field('qualityexpert.id,qualityexpert.year,qualityexpert.term,rtrim(teachers.name) teachername,teachers.teacherno,teachers.position,rtrim(positions.value) positionname,
            rtrim(schools.name) schoolname,teachers.school,teachers.sex,rtrim(sexcode.name) sexname,qualityexpert.score,qualityexpert.normalscore,qualityexpert.rem')
            ->where($condition)->order('id')->select();
        $count = $this->query->table('qualityexpert')->where($condition)->count();

        if (is_array($data) && count($data) > 0)
            $result = array('total' => $count, 'rows' => $data);
        return $result;
    }
}