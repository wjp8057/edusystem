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

    public function  getList($page=1,$rows=20,$year,$term,$type,$flag='',$school='')
    {
        $result = ['total' => 0, 'rows' => []];
        $condition = null;
        $condition['testplan.year'] = $year;
        $condition['testplan.term'] = $term;
        $condition['testplan.type'] = $type;
        if($flag!='') $condition['testplan.flag'] = $flag;
        if($school!='') $condition['courses.school'] = $school;
        $data = $this->query->table('testplan')
            ->join('testbatch', 'testbatch.flag=testplan.flag and testbatch.year=testplan.year and testplan.term=testbatch.term and testbatch.type=testplan.type')
            ->join('courses', 'courses.courseno=substring(testplan.courseno,1,7)')
            ->join('schools','schools.school=courses.school')
            ->field('id,testplan.year,testplan.term,testplan.courseno,rtrim(courses.coursename) coursename,testbatch.testtime,
            rtrim(roomno1) roomno1,rtrim(roomno2) roomno2,rtrim(roomno3) roomno3,
            room1,room2,room3,seats1,seats2,seats3,attendents,schools.school,rtrim(schools.name) schoolname')
            ->order('courseno')->page($page, $rows)->where($condition)->select();
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
                where t2.id=:id and  t1.year=t2.year and t1.term=t2.term and t1.flag=t2.flag and t1.type=t2.type)';
        $result=Db::query($sql,$bind);
        if(count($result)==0)
            return true;
        else
            return false;
    }
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
}