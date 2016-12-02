<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:48
 */

namespace app\common\service;


use app\common\access\Item;
use app\common\access\MyAccess;
use app\common\access\MyException;
use app\common\access\MyService;
use app\common\vendor\DrCom;
use think\Db;
use think\Exception;
use app\common\vendor\PHPExcel;

/**选课记录
 * Class R32
 * @package app\common\service
 */
class R32 extends  MyService {
    //获取课程选修学生列表
    public function getStudentList($page=1,$rows=20,$year='',$term='',$courseno=''){
        if($year==''||$term==''||$courseno=='')
            throw new Exception('year term courseno is empty ',MyException::PARAM_NOT_CORRECT);

        $result=null;
        $condition=null;
        $condition['courseno']=substr($courseno,0,7);
        $condition['group']=substr($courseno,7,2);
        $condition['r32.year']=$year;
        $condition['r32.term']=$term;
        $count= $this->query->table('r32')->where($condition)->count();// 查询满足要求的总记录数
        $data=$this->query->table('r32')->join('students ',' students.studentno=r32.studentno')
            ->join('approachcode  ',' approachcode.code=r32.approach')->where($condition)->page($page,$rows)
            ->join('classes  ',' classes.classno=students.classno')
            ->join('sexcode  ',' students.sex=sexcode.code')
            ->join('schools  ',' schools.school=classes.school')
            ->field('r32.approach,rtrim(approachcode.name) as approachname,rtrim(students.studentno) studentno,
            rtrim(students.name) studentname,rtrim(classes.classname) classname,rtrim(sexcode.name) sexname,rtrim(students.sex) sex,rtrim(schools.name) schoolname')
            ->order('studentno')->select();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
    //根据课号导出考勤表
    public function exportCheckInByCourseno($year,$term,$courseno)
    {
        $file = "考勤表";
        $array=[];
        $result = $this->getStudentList(1, 10000, $year, $term, $courseno);
        $data = $result['rows'];
        $title = "宁波城市学院课堂考勤记录表";
        $schedule = new ViewSchedule();
        $course = $schedule->getCourseInfo($year, $term, $courseno);
        $sheet = $course['coursename'];
        $info = "课号:" . $course['courseno'] . '  课名:' . $course['coursename'] .
            '   教师:' . $course['teachername'] . '   班级:' . $course['classname'] . " 人数:" . $course['attendents'];

        $template = array("studentno" => "学号", "studentname" => "姓名", "classname" => "班级");
        $string = array("studentno");
        $array[] = array("sheet" => $sheet, "title" => $title, "info" => $info, "template" => $template, "data" => $data, "string" => $string);
        PHPExcel::printCourseCheckIn($file,$array);
    }
    //根据教师号导出考勤表
    public function exportCheckInByTeacherno($year,$term,$teacherno)
    {
        $file = "考勤表";
        $array=[];
        $schedule=new ViewScheduleTable();
        $result=$schedule->getTeacherCourseList(1,1000,$year,$term,$teacherno);
        $courserows=$result['rows'];
        foreach($courserows as $one) {
            $courseno=$one['courseno'];
            $result = $this->getStudentList(1, 10000, $year, $term, $courseno);
            $data = $result['rows'];
            $title = "宁波城市学院课堂考勤记录表";
            $schedule = new ViewSchedule();
            $course = $schedule->getCourseInfo($year, $term, $courseno);
            $sheet = $course['coursename'];
            $info = "课号:" . $course['courseno'] . '  课名:' . $course['coursename'] .
                '   教师:' . $course['teachername'] . '   班级:' . $course['classname'] . " 人数:" . $course['attendents'];

            $template = array("studentno" => "学号", "studentname" => "姓名", "classname" => "班级");
            $string = array("studentno");
            $array[] = array("sheet" => $sheet, "title" => $title, "info" => $info, "template" => $template, "data" => $data, "string" => $string);
        }
        PHPExcel::printCourseCheckIn($file,$array);
    }
    //禁用选课学生的网络
    public function stopNetWork($year,$term,$courseno){
        set_time_limit(300);
        $result=null;
        $success=0;
        $fail=0;
        $order=0;
        $condition=null;
        $condition['courseno']=substr($courseno,0,7);
        $condition['group']=substr($courseno,7,2);
        $condition['r32.year']=$year;
        $condition['r32.term']=$term;
        if(!MyAccess::checkCourseTeacher($year,$term,$courseno))
            return array('info' =>'您无法停用其他教师的课程学生网络', 'status' =>0);
        $data=$this->query->table('r32')
            ->field('studentno')->where($condition)->select();
        if(is_array($data)&&count($data)>0) {
            foreach($data as $one){
                if(DrCom::stop($one['studentno'],$order)) {
                    $success++;
                }
                else
                    $fail++;
                $order++;
            }
        }
        if($fail>0){
            $status=0;
            $info=$success."个踢网成功，".$fail."个失败";
        }
        else {
            $status=1;
            $info=$success."个踢网成功!";
        }
        return array('info' => $info, 'status' => $status);
    }

    //更新选课人数
    private static function updateAttendent($year,$term,$courseno){
        $condition=null;
        $condition['year']=$year;
        $condition['term']=$term;
        $condition['courseno+[group]']=$courseno;
        $amount=Db::table('r32')->where($condition)->count();
        $data['attendents']=$amount;
        Db::table('scheduleplan')->where($condition)->update($data);
    }
    //检查是否管理部门
    private static function checkManage(){
        if(session('S_LOGIN_TYPE')==1&&session('S_MANAGE')==1)
            return true;
        else
            return false;
    }
    //检测是否已缴费 true 已缴纳，false  未缴纳
    private static function checkFee($studentno){
        $condition=null;
        $condition['studentno']=$studentno;
        $result=Db::table('students')->where($condition)->field('free')->find();

        if(is_array($result)&&$result['free']==0)
            return true;
        else
            return false;

    }
    //本学期是否已经选过该课程 true 已经选过 false 未选
    private static function isSelected($year,$term,$courseno,$studentno){
        $condition=null;
        $condition['year']=$year;
        $condition['term']=$term;
        $condition['studentno']=$studentno;
        $condition['courseno']=substr($courseno,0,7);
        $result=Db::table('r32')
            ->field('courseno')
            ->where($condition)->find();
        if(is_array($result))
            return true;
        else
            return false;
    }
    //获取课程的类型，必修课、模块课、选修课，如果找不到，默认为选修课
    private  static function  getCourseType($courseno,$studentno){
        $condition=null;
        $condition['studentno']=$studentno;
        $condition['courseno']=substr($courseno,0,7);
        $result=Db::table('studentplan')
            ->join('r30','r30.majorplan_rowid=studentplan.majorplanid')
            ->join('r12','r12.programno=r30.progno')
            ->field('coursetype')
            ->where($condition)->find();
        if(is_array($result))
            return ['in'=>1,'type'=>$result['coursetype']];
        else
            return ['in'=>0,'type'=>'E'];
    }
    //获取课程的考核方式，默认为考试
    private  static function  getExamType($year,$term,$courseno){
        $condition=null;
        $condition['year']=$year;
        $condition['term']=$term;
        $condition['courseno+[group]']=$courseno;
        $result=Db::table('courseplan')
            ->field('examtype')
            ->where($condition)->find();
        if(is_array($result))
            return $result['examtype'];
        else
            return 'T';
    }
    //是否重修
    private static function isRepeat($courseno,$studentno){
        $condition=null;
        $condition['studentno']=$studentno;
        $condition['courseno']=substr($courseno,0,7);
        $result=Db::table('r32')
            ->field('courseno')
            ->where($condition)->find();
        if(is_array($result))
            return true;
        else
            return false;
    }
    //检查学生的选课是否存在冲突
    private  static function  hasConflict($year,$term,$studentno){
        $bind=["year"=>$year,"term"=>$term,'studentno'=>$studentno];
        //完全重复
        $sql="select weeks&OEWOPTIONS.TIMEBIT2,day,time
            from schedule inner join r32 on r32.courseno=schedule.courseno and r32.[group]=schedule.[group] and r32.year=schedule.year and r32.term=schedule.term
            inner join oewoptions on OEWOPTIONS.code=schedule.oew
            where  day!=0 and schedule.year=:year and schedule.term=:term and r32.studentno=:studentno
            group by weeks&OEWOPTIONS.TIMEBIT2,day,time
            having count(*)>1";
        $row=Db::query($sql,$bind);
        if(count($row)>0)
            return true;
        else{
            //部分周次重复
            $sql="select day,time,dbo.GROUP_AND(week) as week  from(
                select weeks&OEWOPTIONS.TIMEBIT2 week,day,TIMESECTIONS.TIMEBITS time
                from schedule inner join r32 on r32.courseno=schedule.courseno and r32.[group]=schedule.[group] and r32.year=schedule.year and r32.term=schedule.term
                inner join oewoptions on OEWOPTIONS.code=schedule.oew
                inner join TIMESECTIONS on TIMESECTIONS.name=SCHEDULE.time
                where  day!=0 and schedule.year=:year and schedule.term=:term and r32.studentno=:studentno
                group by weeks&OEWOPTIONS.TIMEBIT2 ,day,TIMESECTIONS.TIMEBITS
                having count(*)=1) as t
                group by day,time
                having count(*)>1 and dbo.GROUP_AND(week)>0";
            $row=Db::query($sql,$bind);
            if(count($row)>0)
                return true;
        }
        return false;
    }
    //学生自主选课
    public function updateByStudent($postData){
        $info='';
        $status=1;
        $year=$postData['year'];
        $term=$postData['term'];
        $studentno=session('S_USER_NAME');
        //1、判断学生是否已经缴费
        if(!self::checkFee($studentno)){
            return ['info'=>'您存在欠费，无法选课或退课！','status'=>0];
        }
        if(!QualityStudentDetail::isAllDone($year,$term,$studentno))
            return ['info'=>'您尚未完成本学期学评教！','status'=>0];
        //选课
        if (isset($postData["inserted"])) {
            $updated = $postData["inserted"];
            $listUpdated = json_decode($updated);
            foreach ($listUpdated as $one) {
                $condition = null;
                $courseno=$one->courseno;
                if(self::isSelected($year,$term,$courseno,$studentno)){
                    $info.='失败，'.$courseno.'本学期已选修前7位课号相同课程！<br/>';
                    $status=0;
                    continue; //进入下一个课程。
                }
                //2、判断是否锁定，如果锁定了且非管理员就退出
                $course=Item::getSchedulePlanItem($year,$term,$courseno);
                if($course['halflock']==1)
                {
                    $info.='失败，'.$courseno.'已锁定！<br/>';
                    $status=0;
                    continue; //进入下一个课程。
                }
                //3、如果有人数上限，是否已经选满。
                if($course['lock']==1&&$course['estimate']<$course['attendents'])
                {
                    $info.='失败，'.$courseno.'已达到人数上限！<br/>';
                    $status=0;
                    continue; //进入下一个课程。
                }
                //获取教学计划类型
                $program=self::getCourseType($courseno,$studentno);
                $data['coursetype']=$program['type'];
                $data['inprogram']=$program['in'];
                $data['repeat']=self::isRepeat($courseno,$studentno);
                $data['examtype']=self::getExamType($year,$term,$courseno);
                $data['year']=$year;
                $data['term']=$term;
                $data['studentno']=$studentno;
                $data['courseno']=substr($courseno,0,7);
                $data['group']=substr($courseno,7,2);
                $this->query->table('r32')->insert($data);
                $info.=$courseno.'选课操作成功！<br/>';
                //更新课程人数
                self::updateAttendent($year,$term,$courseno);
            }
            //检查是否有冲突。
            if(self::hasConflict($year,$term,$studentno))
            {
                $info.='部分课程存在冲突！<br/>';
                $status=0;
            }
        }

        if (isset($postData["deleted"])) {
            $updated = $postData["deleted"];
            $listUpdated = json_decode($updated);
            foreach ($listUpdated as $one) {
                $condition = null;
                $courseno = $one->courseno;
                //判断是否锁定，如果锁定了且非管理员就退出
                $course = Item::getSchedulePlanItem($year, $term, $courseno);
                if ($course['halflock'] == 1) {
                    $info .= '失败，' . $courseno . '已锁定！<br/>';
                    $status = 0;
                    continue; //进入下一个课程。
                }
                $condition = null;
                $condition['year'] = $year;
                $condition['term'] = $term;
                $condition['studentno'] = $studentno;
                $condition['courseno'] = substr($courseno, 0, 7);
                $condition['[group]'] = substr($courseno, 7, 2);
                $this->query->table('r32')->where($condition)->delete();
                $info.=$courseno.'退课成功！</br>';
                //更新课程人数
                self::updateAttendent($year, $term, $courseno);
            }
        }
        return ['info'=>$info,'status'=>$status];
    }
    //检索学生的选课
    public function getSelectedCourse($page=1,$rows=20,$year,$term,$studentno){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['r32.year']=$year;
        $condition['r32.term']=$term;
        $condition['r32.studentno']=$studentno;
        $data=$this->query->table('r32')
            ->join('viewscheduletable vt','r32.courseno+r32.[group]=vt.coursenogroup and vt.year=r32.year and vt.term=r32.term')
            ->join('plantypecode','plantypecode.code=r32.coursetype')
            ->join('examoptions','examoptions.name=r32.examtype')
            ->where($condition)->page($page,$rows)
            ->field("coursenogroup,vt.year,vt.term,credits,examoptions.value  examtype,plantypecode.name coursetype,weekhours,rem,
            dbo.GROUP_CONCAT(distinct rtrim(classname),',') classname,dbo.GROUP_CONCAT(distinct rtrim(teachername),',') teachername,
            dbo.GROUP_CONCAT(distinct dayntime,',') dayntime,schoolname,coursename")
            ->group('coursenogroup,vt.year,vt.term,credits,schoolname,rem,weekhours,schoolname,coursename,examoptions.value,plantypecode.name')
            ->order('coursenogroup')
            ->select();
        $count= $this->query->table('r32')->where($condition)->count();// 查询满足要求的总记录数
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

}