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
            ->join('selective','selective.year=r32.year and selective.term=r32.term and selective.studentno=r32.studentno','LEFT')
            ->field('r32.approach,rtrim(approachcode.name) as approachname,rtrim(students.studentno) studentno,
            rtrim(students.name) studentname,rtrim(classes.classname) classname,rtrim(sexcode.name) sexname,rtrim(students.sex) sex,rtrim(schools.name) schoolname,
            credit,amount,termcredit,termamount,publiccredit+creativecredit publiccredit,r32.repeat,0 as del ')
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
    public static  function getPublicAmount($year,$term,$studentno){
        $condition=null;
        $condition['studentno']=$studentno;
        $condition['year']=$year;
        $condition['term']=$term;
        $result=Db::table('r32')->where($condition)->where("courseno like '08%'")->count();
        return $result;
    }
    //以学生为主体选课
    public function selectByStudent($postData){
        $info='';
        $status=1;
        $year=$postData['year'];
        $term=$postData['term'];
        $reason="学生本人退课！";
        if(session('S_LOGIN_TYPE')==2) {
            $studentno = session('S_USER_NAME');
            if(!QualityStudentDetail::isAllDone($year,$term,$studentno))
                return ['info'=>$studentno.'尚未完成本学期学评教！','status'=>0];
        }
        else {
            $studentno = $postData['studentno'];
            if(!MyAccess::checkStudentSchool($studentno))
                return ['info'=>'您无法修改其他学院学生'.$studentno.'的选课','status'=>"0"];
            $reason="管理人员".session('S_REAL_NAME')."退课";
        }
        //1、判断学生是否已经缴费
        if(!self::checkFee($studentno)){
            return ['info'=>$studentno.'存在欠费，无法选课或退课！','status'=>0];
        }

        //选课
        if (isset($postData["inserted"])) {
            $updated = $postData["inserted"];
            $listUpdated = json_decode($updated);
            foreach ($listUpdated as $one) {
                $condition = null;
                $courseno=$one->courseno;
                if(substr($courseno,0,2)=="08"&&self::getPublicAmount($year,$term,$studentno)>=4)
                {
                    $info.='失败，'.$courseno.'本学期已选修前3门及以上08开头的公选课，请适当退课！<br/>';
                    $status=0;
                    continue; //进入下一个课程。
                }
                if(self::isSelected($year,$term,$courseno,$studentno)){
                    $info.='失败，'.$courseno.'本学期已选修前7位课号相同课程！<br/>';
                    $status=0;
                    continue; //进入下一个课程。
                }
                //2、判断是否锁定，如果锁定了且非管理员就退出
                $course=Item::getSchedulePlanItem($year,$term,$courseno);
                if($course['halflock']==1&&session('S_MANAGE')!=1)
                {
                    $info.='失败，'.$courseno.'已锁定！<br/>';
                    $status=0;
                    continue; //进入下一个课程。
                }
                //3、如果有人数上限，是否已经选满。
                if($course['lock']==1&&$course['estimate']<=$course['attendents'])
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
                if ($course['halflock'] == 1&&session('S_MANAGE')!=1) {
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
                R32Dump::toDump($year,$term,$courseno,$studentno,$reason);
                $this->query->table('r32')->where($condition)->delete();
                $info.=$courseno.'退课成功！</br>';
                //更新课程人数
                self::updateAttendent($year, $term, $courseno);
            }
        }

        $selective=new Selective();
        $selective->update($year,$term,$studentno);
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
    //选课管理中筛选名单与修改学生修课方式。
    public function update($postData){
        set_time_limit(0);
        $info='';
        $status=1;
        $updateRow=0;
        $deleteRow=0;
        $errorRow=0;
        $insertRow=0;
        $courseno=$postData["courseno"];
        $year=$postData["year"];
        $term=$postData["term"];
        $isCourseSchool=MyAccess::checkCourseSchool($courseno);

        if (isset($postData["updated"])) {
            $updated = $postData["updated"];
            $listUpdated = json_decode($updated);
            foreach ($listUpdated as $one) {
                $condition = null;
                $data=null;
                $studentno=$one->studentno;
                if($isCourseSchool||MyAccess::checkStudentSchool($studentno)) {
                    $condition['year'] = $year;
                    $condition['term'] = $term;
                    $condition['courseno'] = substr($courseno, 0, 7);
                    $condition['group'] = substr($courseno, 7, 2);
                    $condition['studentno'] = $studentno;
                    $data['approach'] = $one->approach;
                    $this->query->table('r32')->where($condition)->update($data);
                    $updateRow++;
                }
                else {
                    $status=0;
                    $info .= '修改失败，你既不是课程' . $courseno . '的开课学院也不是学生' . $studentno . '所在学院！<br/>';
                }
            }
        }
        if (isset($postData["deleted"])) {
            $updated = $postData["deleted"];
            $listUpdated = json_decode($updated);
            $course=Item::getSchedulePlanItem($year,$term,$courseno);
            if($course['halflock']==1&&session('S_MANAGE')!=1)
            {
                $info.='删除失败，'.$courseno.'课程已锁定！<br/>';
                $status=0;
                $errorRow++;
            }
            else{
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $studentno=$one->studentno;
                    if($isCourseSchool||MyAccess::checkStudentSchool($studentno)) {
                        $condition['year'] = $year;
                        $condition['term'] = $term;
                        $condition['courseno'] = substr($courseno, 0, 7);
                        $condition['group'] = substr($courseno, 7, 2);
                        $condition['studentno'] = $studentno;
                        //写入r32dump
                        R32Dump::toDump($year, $term, $courseno, $studentno, '课程限制，人数过多！');
                        $this->query->table('r32')->where($condition)->delete();
                        $deleteRow++;
                    }
                    else {
                        $status=0;
                        $info .= '删除失败，你既不是课程' . $courseno . '的开课学院也不是学生' . $studentno . '所在学院！<br/>';
                    }
                }
                self::updateAttendent($year,$term,$courseno);
                $selective=new Selective();
                $selective->update($year,$term,'%');
            }
        }
        if (isset($postData["inserted"])) {
            $updated = $postData["inserted"];
            $listUpdated = json_decode($updated);
            $course=Item::getSchedulePlanItem($year,$term,$courseno);
            if($course['halflock']==1&&session('S_MANAGE')!=1)
            {
                $info.='添加失败，'.$courseno.'课程已锁定！<br/>';
                $status=0;
                $errorRow++;
            }
            else if($course['lock']==1&&((int)$course['estimate']<(int)$course['attendents']+count($listUpdated)))
            {
                $info.='失败，'.$courseno.'预计人数'.$course['estimate'].',已选'.$course['attendents'].',本次'.count($listUpdated).',超过人数上限！<br/>';
                $status=0;
                $errorRow++;
            }
            else{
                $examtype=self::getExamType($year,$term,$courseno);
                foreach ($listUpdated as $one) {
                    //获取教学计划类型
                    $studentno=$one->studentno;

                      if(!self::checkFee($studentno)){
                        $info.=$studentno."欠费，无法选课！";
                        $status=0;
                        continue;
                    }
                    if($isCourseSchool||MyAccess::checkStudentSchool($studentno)) {
                        $program = self::getCourseType($courseno, $studentno);
                        $data['coursetype'] = $program['type'];
                        $data['inprogram'] = $program['in'];
                        $data['repeat'] = self::isRepeat($courseno, $studentno);
                        $data['examtype'] = $examtype;
                        $data['year'] = $year;
                        $data['term'] = $term;
                        $data['studentno'] = $studentno;
                        $data['courseno'] = substr($courseno, 0, 7);
                        $data['group'] = substr($courseno, 7, 2);
                        $this->query->table('r32')->insert($data);
                        $insertRow++;
                        $selective = new Selective();
                        $selective->update($year, $term, $one->studentno);
                    }
                    else {
                        $status=0;
                        $info .= '添加失败，你既不是课程' . $courseno . '的开课学院也不是学生' . $studentno . '所在学院！<br/>';
                    }
                }
                self::updateAttendent($year,$term,$courseno);
            }
        }
        if($updateRow+$deleteRow+$errorRow+$insertRow==0&&$info==''){
            $status=0;
            $info="没有数据更新";
        }
        else {
            if ($updateRow > 0) $info .= $updateRow . '条更新！</br>';
            if ($deleteRow > 0) $info .= $deleteRow . '条删除！</br>';
            if ($insertRow > 0) $info .= $insertRow . '条添加！</br>';
        }
        return ['info'=>$info,'status'=>$status];
    }
    //   按班级统一选必修课，模块课
    public static  function selectAll($year,$term,$classno,$type){
         MyAccess::checkAccess('E');
        $bind=["year"=>$year,"term"=>$term,'classno'=>$classno,'type'=>$type];
        //完全重复
        $sql="insert into r32(year,term,courseno,[group],studentno,inprogram,coursetype,examtype)
            select year,term,courseno,[group],studentno ,1,coursetype,examtype
            from courseplan
            inner join students on students.classno=courseplan.classno
             where year=:year and term=:term and courseplan.classno like :classno and coursetype=:type  and students.status='A'
            and  not exists (select * from r32 where r32.year=courseplan.year and r32.term=courseplan.term
             and r32.courseno+r32.[group]=courseplan.courseno+courseplan.[group]  and r32.studentno=students.studentno)";
        $row=Db::execute($sql,$bind);
        self::updateAttendent($year,$term,'%');
        return array('info'=>"统一选课完成，新增".$row."条记录",'status'=>"1");
    }
    //更新选课人数
    public static  function updateAttendent($year,$term,$courseno){
        $bind=["year"=>$year,"term"=>$term,"year2"=>$year,"term2"=>$term,'courseno'=>$courseno];
        //完全重复
        $sql="update scheduleplan set attendents=isnull(t.amount,0)
          from scheduleplan left  join (select year,term,courseno+[group] courseno,count(*) amount from r32
          where year=:year and term=:term
          group by year,term,courseno+[group]) as t on t.year=scheduleplan.year and t.term=scheduleplan.term
          and t.courseno=scheduleplan.courseno+scheduleplan.[group]
          where scheduleplan.year=:year2 and scheduleplan.term=:term2 and scheduleplan.courseno+scheduleplan.[group] like :courseno";
        Db::execute($sql,$bind);

        $bind=["year"=>$year,"term"=>$term,'courseno'=>$courseno];
        //完全重复
        $sql="update viewscheduletable set attendents=s.attendents
          from viewscheduletable as v inner join scheduleplan as s on s.recno=v.recno
          where s.year=:year and s.term=:term and s.courseno+s.[group] like :courseno";
        Db::execute($sql,$bind);
    }
    //获取类型简称
    private static  function  getTypeName($type){
        switch($type){
            case 'M':
                return '必';
            case 'T':
                return '模';
            case  'E':
                return '选';
            default:
                return '选';
        }
    }
    //班级选课汇总表
    public function selectedTable($year,$term,$classno){
        $condition=null;
        $condition['year']=$year;
        $condition['term']=$term;
        $condition['students.classno']=$classno;
        $course=$this->query->table('r32')
            ->join('students','students.studentno=r32.studentno')
            ->join('courses','courses.courseno=r32.courseno')
            ->field('distinct rtrim(coursename) coursename')
            ->where($condition)
            ->order('coursename')
            ->select();
        //课程数

        $cAmount=count($course);
        $selective=$this->query->table('r32')
            ->join('students','students.studentno=r32.studentno')
            ->join('courses','courses.courseno=r32.courseno')
            ->field('rtrim(r32.studentno) studentno,rtrim(students.name) studentname,rtrim(coursename) coursename,coursetype')
            ->where($condition)
            ->order('studentno,coursename')
            ->select();
        //学生数
        $sAmount=count($selective);
        $content="<tr><td class='studentno'>学号</td><td class='studentname'>姓名</td>";

        for($i=0;$i<$cAmount;$i++)
            $content.="<td class='coursename'>".htmlspecialchars($course[$i]["coursename"])."</td>";
        $content.="</tr>";
        $lastStudent="";
        $lastCourseName="";
        $i=0;
        for($j=0;$j<$sAmount;$j++){
            if($lastStudent!=$selective[$j]['studentno'])
            {
                if($lastStudent!="") {
                    for($q=$i;$q<$cAmount;$q++)
                        $content.="<td>&nbsp;</td>";
                    $content .= "</tr>";
                }
                $lastStudent=$selective[$j]['studentno'];
                $content.="<tr><td>".htmlspecialchars($selective[$j]["studentno"])."</td><td>".htmlspecialchars($selective[$j]["studentname"])."</td>";
                $i=0;
                $lastCourseName='';
            }
            $type="";
            for($v=$i;$v<$cAmount;$v++){
                if($selective[$j]["coursename"]==$course[$v]["coursename"]) {
                    $i=$v+1;
                    $content.="<td>".self::getTypeName($selective[$j]["coursetype"])."</td>";
                    $lastCourseName=$selective[$j]["coursename"];
                    break;
                }
                else if($selective[$j]["coursename"]==$lastCourseName){
                    break;
                }
                else
                    $content.="<td>&nbsp;</td>";
            }
        }
        for($q=$i;$q<$cAmount;$q++)
            $content.="<td>&nbsp;</td>";
        $content.="</tr>";
        return  $content;
    }


    //清除公选课超过限制的学生选课记录 公选课为08开头的课程
    private static function  clearLimit($year,$term,$studentno,$amount=3){

        $bind=["year"=>$year,"term"=>$term,'studentno'=>$studentno,'reason'=>'公共选修课超过'.$amount."门",'amount'=>$amount];
        $sql="insert into r32dump(year,term,courseno,[group],studentno,inprogram,conflicts,confirm,approach,repeat,fee,coursetype,examtype,selecttime,reason)
              select year,term,courseno,[group],studentno,inprogram,conflicts,confirm,approach,repeat,fee,coursetype,examtype,selecttime,:reason
              from r32 where year=:year and term=:term and studentno like :studentno and courseno like '08%'and  exists (
              select * from selective as s where s.year=r32.year and s.term=r32.term and s.studentno=r32.studentno and termamount> :amount)";
        Db::execute($sql,$bind);

        $bind=["year"=>$year,"term"=>$term,'studentno'=>$studentno,'amount'=>$amount];
        //完全重复
        $sql="delete  from r32 where year=:year and term=:term and studentno like :studentno and courseno like '08%'and  exists (
            select * from selective as s where s.year=r32.year and s.term=r32.term and s.studentno=r32.studentno and termamount> :amount)";
        Db::execute($sql,$bind);
    }
    //删除超过限制选课的
    public function deleteOverLimit($year,$term,$studentno='%',$amount=3){
        //删除选课记录
        self::clearLimit($year,$term,$studentno,$amount);
        //更新选课情况表
        $obj=new Selective();
        $obj->update($year,$term,'%');
        return ['status' => "1", 'info' => "删除完成"];
    }
}