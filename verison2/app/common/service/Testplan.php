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
// | Created:2016/11/18 8:42
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyAccess;
use app\common\access\MyService;
use think\Db;

class TestPlan extends MyService {
    /**学生的课程考试安排
     * @param int $page
     * @param int $rows
     * @param $year
     * @param $term
     * @return array
     */
    public function studentCourseList($page=1,$rows=20,$year,$term,$studentno)
    {
        $result = ['total' => 0, 'rows' => []];
        $condition = null;
        $condition['testplan.year'] = $year;
        $condition['testplan.term'] = $term;
        $condition['r32.studentno'] = $studentno;
        $data = $this->query->table('testplan')
            ->join('r32 ', ' r32.year=testplan.year and r32.term=testplan.term and r32.courseno+r32.[group]=testplan.courseno')
            ->join('testbatch', 'testbatch.flag=testplan.flag and testbatch.year=testplan.year and testplan.term=testbatch.term')
            ->join('courses', 'courses.courseno=r32.courseno')
            ->field('testplan.year,testplan.term,testplan.courseno,rtrim(courses.coursename) coursename,testbatch.testtime,roomno1,roomno2,roomno3')
            ->order('testtime')->page($page, $rows)->where($condition)->select();
        $count = $this->query->table('testplan')
            ->join('r32 ', ' r32.year=testplan.year and r32.term=testplan.term and r32.courseno+r32.[group]=testplan.courseno')
            ->where($condition)->count();
        if (is_array($data) && count($data) > 0) { //小于0的话就不返回内容，防止IE下无法解析rows为NULL时的错误。
            $result = array('total' => $count, 'rows' => $data);
        }
        return $result;
    }

    public function  getList($page=1,$rows=20,$year,$term,$type,$flag='',$school='',$studentschool='',$teachername='%',$teacherno='%')
    {
        $result = ['total' => 0, 'rows' => []];
        $condition = null;
        $condition['testplan.year'] = $year;
        $condition['testplan.term'] = $term;
        $condition['testplan.type'] = $type;
        if($flag!='') $condition['testplan.flag'] = $flag;
        if($school!='') $condition['courses.school'] = $school;
        if($studentschool!='') $condition['testplan.studentschool'] = $studentschool;
        if($teachername!='%') $condition['teachername1|teachername2|teachername3|teachername4|teachername5|teachername6']=array('like',$teachername);
        if($teacherno!='%') $condition['teacherno1|teacherno2|teacherno3|teacherno4|teacherno5|teacherno6']=array('like',$teacherno);
        $data = $this->query->table('testplan')
            ->join('testbatch', 'testbatch.flag=testplan.flag and testbatch.year=testplan.year and testplan.term=testbatch.term and testbatch.type=testplan.type')
            ->join('courses', 'courses.courseno=substring(testplan.courseno,1,7)')
            ->join('schools','schools.school=courses.school')
            ->join('schools s','s.school=testplan.studentschool')
            ->field('id,testplan.year,testplan.term,testplan.courseno,rtrim(courses.coursename) coursename,testbatch.testtime,
            rtrim(roomno1) roomno1,rtrim(roomno2) roomno2,rtrim(roomno3) roomno3,room1,room2,room3,
            seats1,seats2,seats3,attendents,schools.school,rtrim(schools.name) schoolname,studentschool,rtrim(s.name) studentschoolname,
            rtrim(teachername1) teachername1, rtrim(teachername2) teachername2, rtrim(teachername3) teachername3, rtrim(teachername4) teachername4, rtrim(teachername5) teachername5,
             rtrim(teachername6) teachername6, rtrim(teachername7) teachername7, rtrim(teachername8) teachername8, rtrim(teachername9) teachername9,
             teacherno1,teacherno2,teacherno3,teacherno4,teacherno5,teacherno6,teacherno7,teacherno8,teacherno9,rtrim(classes) classes,rtrim(testplan.rem) rem')
            ->order('testtime,courseno')->page($page, $rows)->where($condition)->select();
        $count = $this->query->table('testplan')
            ->join('courses', 'courses.courseno=substring(testplan.courseno,1,7)')
            ->where($condition)->count();
        if (is_array($data) && count($data) > 0) { //小于0的话就不返回内容，防止IE下无法解析rows为NULL时的错误。
            $result = array('total' => $count, 'rows' => $data);
        }
        return $result;
    }
    //检查教室冲突
    private  static function checkRoomUsed($id,$roomno)
    {
        $bind=['id'=>$id,'roomno1'=>$roomno,'roomno2'=>$roomno,'roomno3'=>$roomno];
        $sql='select t1.id from testplan as t1
                where (room1=:roomno1 or room2=:roomno2 or room3=:roomno3) and  exists (select * from testplan as t2
                where t2.id=:id and t1.id!=t2.id and  t1.year=t2.year and t1.term=t2.term and t1.flag=t2.flag and t1.type=t2.type)';
        $result=Db::query($sql,$bind);
        if(count($result)==0)
            return true;
        else
            return false;
    }
    //设置考场
    public function  updateRoom($postData){
        $updateRow=0;
        $deleteRow=0;
        $insertRow=0;
        $errorRow=0;
        $info="";
        $used="";
        $status=1;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            $force=isset($postData["force"])?$postData["force"]:0;
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $data = null;
                    $condition['id'] = $one->id;
                    $condition['courseno'] = $one->courseno;
                    $data['room1']=$one->room1;
                    $data['roomno1']=$one->roomno1;
                    $data['seats1']=$one->seats1;
                    $data['room2']=$one->room2;
                    $data['roomno2']=$one->roomno2;
                    $data['seats2']=$one->seats2;
                    $data['room3']=$one->room3;
                    $data['roomno3']=$one->roomno3;
                    $data['seats3']=$one->seats3;
                    if($force!=1&&!self::checkRoomUsed($one->id,$one->room1))
                        $used.=$one->roomno1.'教室已有安排！';
                    if($force!=1&&!self::checkRoomUsed($one->id,$one->room2))
                        $used.=$one->roomno2.'教室已有安排！';
                    if($force!=1&&!self::checkRoomUsed($one->id,$one->room3))
                        $used.=$one->roomno3.'教室已有安排！';

                    if(MyAccess::checkCourseSchool($one->courseno)&&$used=='')
                        $updateRow += $this->query->table('testplan')->where($condition)->update($data);
                    else{
                        $info.=$one->courseno.'不是本学院课程，无法更改信息'.$used;
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
        }
        $result=array('info'=>$info,'status'=>$status,'used'=>$used);
        return $result;
    }
    //检查同时段教师是否已经有监考
    private  static function checkTeacherUsed($id,$teacherno)
    {
        $bind=['id'=>$id,'teacherno1'=>$teacherno,'teacherno2'=>$teacherno,'teacherno3'=>$teacherno,'teacherno4'=>$teacherno,'teacherno5'=>$teacherno,'teacherno6'=>$teacherno];
        $sql='select t1.id from testplan as t1
                where (teacherno1=:teacherno1 or teacherno2=:teacherno2 or teacherno3=:teacherno3 or teacherno4=:teacherno4 or teacherno5=:teacherno5 or teacherno6=:teacherno6)
                 and  exists (select * from testplan as t2
                where t2.id=:id  and t2.id!=t1.id and  t1.year=t2.year and t1.term=t2.term and t1.flag=t2.flag and t1.type=t2.type)';
        $result=Db::query($sql,$bind);
        if(count($result)==0)
            return true;
        else
            return false;
    }
    //设置监考教师
    public function  updateTeacher($postData){
        $updateRow=0;
        $deleteRow=0;
        $insertRow=0;
        $errorRow=0;
        $info="";
        $used="";
        $status=1;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            $force=isset($postData["force"])?$postData["force"]:0;
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $data = null;
                    $condition['id'] = $one->id;
                    $condition['courseno'] = $one->courseno;
                    $data['teacherno1']=$one->teacherno1;
                    $data['teachername1']=$one->teachername1;
                    $data['teacherno2']=$one->teacherno2;
                    $data['teachername2']=$one->teachername2;
                    $data['teacherno3']=$one->teacherno3;
                    $data['teachername3']=$one->teachername3;
                    $data['teacherno4']=$one->teacherno4;
                    $data['teachername4']=$one->teachername4;
                    $data['teacherno5']=$one->teacherno5;
                    $data['teachername5']=$one->teachername5;
                    $data['teacherno6']=$one->teacherno6;
                    $data['teachername6']=$one->teachername6;
                    $data['teacherno7']=$one->teacherno7;
                    $data['teachername7']=$one->teachername7;
                    $data['teacherno8']=$one->teacherno8;
                    $data['teachername8']=$one->teachername8;
                    $data['teacherno9']=$one->teacherno9;
                    $data['teachername9']=$one->teachername9;
                    if($force!=1&&!self::checkTeacherUsed($one->id,$one->teacherno1))
                        $used.=$one->teachername1.'教师已有监考！';
                    if($force!=1&&!self::checkTeacherUsed($one->id,$one->teacherno2))
                        $used.=$one->teachername2.'教师已有监考！';
                    if($force!=1&&!self::checkTeacherUsed($one->id,$one->teacherno3))
                        $used.=$one->teachername3.'教师已有监考！';
                    if($force!=1&&!self::checkTeacherUsed($one->id,$one->teacherno4))
                        $used.=$one->teachername4.'教师已有监考！';
                    if($force!=1&&!self::checkTeacherUsed($one->id,$one->teacherno5))
                        $used.=$one->teachername5.'教师已有监考！';
                    if($force!=1&&!self::checkTeacherUsed($one->id,$one->teacherno6))
                        $used.=$one->teachername6.'教师已有监考！';
                    if(MyAccess::checkTestPlanSchool($one->id)&&$used=='')
                        $updateRow += $this->query->table('testplan')->where($condition)->update($data);
                    else{
                        $info.=$one->courseno.'不是本学院课程，无法更改信息'.$used;
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
        }
        $result=array('info'=>$info,'status'=>$status,'used'=>$used);
        return $result;
    }

    //    设置备注
    public function  updateRem($postData){
        $updateRow=0;
        $errorRow=0;
        $info="";
        $used="";
        $status=1;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $data = null;
                    $condition['id'] = $one->id;
                    $condition['courseno'] = $one->courseno;
                    $data['rem']=$one->rem;
                    if(MyAccess::checkCourseSchool($one->courseno))
                        $updateRow += $this->query->table('testplan')->where($condition)->update($data);
                    else{
                        $info.=$one->courseno.'不是本学院课程，无法更改信息'.$used;
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
        if($updateRow+$errorRow==0){
            $status=0;
            $info="没有数据更新";
        }
        else {
            if ($updateRow > 0) $info .= $updateRow . '条更新！</br>';
        }
        $result=array('info'=>$info,'status'=>$status,'used'=>$used);
        return $result;
    }

}