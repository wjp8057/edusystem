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

/**教学计划课程
 * Class R12
 * @package app\common\service
 */
class R12 extends MyService{

    /**读取
     * @param int $page
     * @param int $rows
     * @return array|null
     */
    function getList($page=1,$rows=20,$programno,$courseno='%',$coursename='%',$school=''){
        $result=null;
        $condition=null;
        $condition['r12.programno']=$programno;
        if($courseno!='%') $condition['r12.courseno']=array('like',$courseno);
        if($coursename!='%') $condition['courses.coursename']=array('like',$coursename);
        if($school!='') $condition['courses.school']=$school;
        $data=$this->query->table('r12')
            ->join('courses','courses.courseno=r12.courseno')
            ->join('courseapproaches ct','ct.name=r12.coursetype')
            ->join('examoptions et','et.name=r12.examtype')
            ->join('schools','schools.school=courses.school')
            ->join('coursecat','coursecat.name=r12.category')
            ->join('testlevel','testlevel.name=r12.test')
            ->page($page,$rows)
            ->field('r12.courseno,r12.courseno oldcourseno,rtrim(courses.coursename) coursename,convert(varchar(10),courses.credits) credits,convert(varchar(10),courses.hours)  hours,
                schools.school,rtrim(schools.name) schoolname,r12.year ,r12.term,r12.examtype,rtrim(et.value) examtypename,r12.coursetype,rtrim(ct.value) coursetypename,test,
                rtrim(testlevel.value) testname,category,rtrim(coursecat.value) categoryname,r12.weeks')
            ->where($condition)->order('year,term,courseno')->select();
        $count= $this->query->table('r12')->join('courses','courses.courseno=r12.courseno')->where($condition)->count();
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
        $programno=$postData["programno"];
        if(!MyAccess::checkProgramSchool($programno))
        {
            return array('info'=>'无法为其他学院的教学计划更新课程','status'=>0);
        }
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["inserted"])) {
                $updated = $postData["inserted"];
                $listUpdated = json_decode($updated);
                $data = null;
                foreach ($listUpdated as $one) {
                    $data['programno'] = $programno;
                    $data['courseno'] = $one->courseno;
                    $data['year'] = $one->year;
                    $data['term'] = $one->term;
                    $data['coursetype'] = $one->coursetype;
                    $data['examtype'] = $one->examtype;
                    $data['test'] = $one->test;
                    $data['category'] = $one->category;
                    $data['weeks'] = $one->weeks;
                    $row = $this->query->table('r12')->insert($data);
                    if ($row > 0)
                        $insertRow++;
                }
            }
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['programno'] = $programno;
                    $condition['courseno'] = $one->oldcourseno;
                    $data['courseno'] = $one->courseno;
                    $data['year'] = $one->year;
                    $data['term'] = $one->term;
                    $data['coursetype'] = $one->coursetype;
                    $data['examtype'] = $one->examtype;
                    $data['test'] = $one->test;
                    $data['category'] = $one->category;
                    $data['weeks'] = $one->weeks;
                    $updateRow += $this->query->table('r12')->where($condition)->update($data);
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['programno'] = $programno;
                    $condition['courseno'] = $one->courseno;
                    $deleteRow += $this->query->table('r12')->where($condition)->delete();
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