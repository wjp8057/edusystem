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
// | Created:2016/6/15 9:26
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyAccess;
use app\common\access\MyService;
use think\Db;

/**排课计划
 * Class SchedulePlan
 * @package app\common\service
 */
class SchedulePlan extends MyService {
    /**获取
     * @param int $page
     * @param int $rows
     * @param $year
     * @param $term
     * @param string $courseno
     * @param string $coursename
     * @param string $classno
     * @param string $school
     * @param array $extracondtion 额外条件
     * @return array|null
     */
    public function getList($page=1,$rows=20,$year,$term,$courseno='%',$coursename='%',$classno='%',$school='',$extracondtion=[]){
        $condition=null;
        $result=['total'=>0,'rows'=>[]];
        $condition['scheduleplan.year']=$year;
        $condition['scheduleplan.term']=$term;
        if($courseno!='%') $condition['scheduleplan.courseno+scheduleplan.[group]']=array('like',$courseno);
        if($coursename!='%') $condition['courses.coursename']=array('like',$coursename);
        if($classno!='%')  $condition['courseplan.classno']=array('like',$classno);
        if($school!='')$condition['courses.school']=$school;
        $data=$this->query->table('scheduleplan')->join('courses','courses.courseno=scheduleplan.courseno')
            ->join('courseplan ','courseplan.year=scheduleplan.year and courseplan.term=scheduleplan.term and courseplan.courseno+courseplan.[group]=scheduleplan.courseno+scheduleplan.[group]')
            ->join('examoptions','examoptions.name=courseplan.examtype')
            ->join('schools ',' schools.school=courses.school')
            ->join('classes ',' classes.classno=courseplan.classno')
            ->where($condition)->group('examoptions.value,scheduleplan.exam,scheduleplan.recno,scheduleplan.courseno+scheduleplan.[group],rtrim(courses.coursename), rtrim(schools.name),schools.school,scheduleplan.estimate,scheduleplan.attendents,halflock,lock')
            ->field("scheduleplan.recno,scheduleplan.courseno+scheduleplan.[group] as courseno,rtrim(courses.coursename) as coursename, rtrim(schools.name) schoolname,schools.school,
                scheduleplan.estimate,scheduleplan.attendents,halflock,lock ,dbo.GROUP_CONCAT(rtrim(classes.classname),' ') as classname,rtrim(examoptions.value) examtypename,scheduleplan.exam")
            ->page($page,$rows)->where($extracondtion)->order('courseno')
            ->select();
        $count= $this->query->table('scheduleplan')->join('courses','courses.courseno=scheduleplan.courseno')
            ->join('courseplan ','courseplan.year=scheduleplan.year and courseplan.term=scheduleplan.term and courseplan.courseno+courseplan.[group]=scheduleplan.courseno+scheduleplan.[group]')
            ->join('schools ',' schools.school=courses.school')
            ->join('classes ',' classes.classno=courseplan.classno')
            ->where($condition)->where($extracondtion)->count('distinct scheduleplan.courseno+scheduleplan.[group]');
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

    /**更新锁定状态
     * @param $postData
     * @return array
     * @throws \Exception
     */
    public function updateStatus($postData){
        $updateRow=0;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['recno'] = $one->recno;
                    $data['halflock'] = $one->halflock;
                    $data['lock'] = $one->lock;
                    $updateRow += $this->query->table('scheduleplan')->where($condition)->update($data);
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
        $status=1;
        if($info=='') {
            $info="没有数据被更新";
            $status=0;
        }
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }

    public function updateAllStatus($year,$term,$halflock,$lock){
        try {
            $condition['year']=$year;
            $condition['term']=$term;
            $data['halflock']=$halflock;
            $data['lock']=$lock;
            $this->query->table('scheduleplan')->where($condition)->update($data);
        }
        catch(\Exception $e){
            $this->query->rollback();
            throw $e;
        }
        $result=array('info'=>"设置成功",'status'=>1);
        return $result;
    }

    public function updateAttendent($year,$term){
        $condition = null;
        $condition['r32.year'] = $year;
        $condition['r32.term'] = $term;
        $subsql = $this->query->table('r32')->where($condition)->field('courseno+[group] courseno,count(*) amount ')->group('courseno+[group]')->buildSql();

        $condition = null;
        $condition['year'] = $year;
        $condition['term'] = $term;
        $data['attendents']=array('exp','t.amount');
        $this->query->table('scheduleplan')->join($subsql.' t','t.courseno=scheduleplan.courseno')->where($condition)->update($data);
    }

    public function updateExam($postData){
        $updateRow=0;
        //更新部分
        //开始事务
        $info='';
        $this->query->startTrans();
        try {
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    if(MyAccess::checkCourseSchool(substr($one->courseno,0,7))){
                        $condition['recno'] = $one->recno;
                        $data['exam'] = $one->exam;
                        $updateRow += $this->query->table('scheduleplan')->where($condition)->update($data);
                    }
                    else{
                        $info.="您无法修改其他学院的课程信息：".$one->courseno;
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
        $status=1;
        if($info=='') {
            $info="没有数据被更新";
            $status=0;
        }
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }
}