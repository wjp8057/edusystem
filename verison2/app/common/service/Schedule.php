<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:57
 */

namespace app\common\service;

use app\common\access\MyException;
use app\common\access\MyService;
use think\Exception;

/**排课
 * Class Schedule
 * @package app\common\service
 */
class Schedule extends MyService {
    private $oew=array(262143=>'',87381=>'(单周)',174762=>'(双周)');
    private $oew_english=array(262143=>'',87381=>'(Week A)',174762=>'(Week B)');
    /**将单条课程信息放入课表数组
     * @param $result
     * @param $one string 数据集
     * @param $string string 特殊字符串
     * @return mixed
     */
    private function  _buildSingle($result,$one,$string){
        $time='('.$one['timename'].')';
        switch($one['time']){
            case 'O':
                $result[$one['day']][1].=$string;
                $result[$one['day']][2].=$string;
                $result[$one['day']][3].=$string;
                $result[$one['day']][4].=$string;
                $result[$one['day']][5].=$string;
                $result[$one['day']][6].=$string;
                break;
            case '1':
            case '2':
                $result[$one['day']][1].=$time.$string;
                break;
            case '3':
            case '4':
                $result[$one['day']][2].=$time.$string;
                break;
            case '5':
            case '6':
                $result[$one['day']][3].=$time.$string;
                break;
            case '7':
            case '8':
                $result[$one['day']][4].=$time.$string;
                break;
            case 'A':
            case 'B':
                $result[$one['day']][5].=$time.$string;
                break;
            case 'C':
            case 'D':
                $result[$one['day']][6].=$time.$string;
                break;
            case 'E':
                $result[$one['day']][1].=$string;
                break;
            case 'F':
                $result[$one['day']][2].=$string;
                break;
            case 'G':
                $result[$one['day']][3].=$string;
                break;
            case 'H':
                $result[$one['day']][4].=$string;
                break;
            case 'I':
                $result[$one['day']][5].=$string;
                break;
            case 'J':
                $result[$one['day']][6].=$string;
                break;
            case 'K':
                $result[$one['day']][3].=$string;
                $result[$one['day']][4].='(第7节)'.$string;
                break;
            case 'L':
                $result[$one['day']][5].=$string;
                $result[$one['day']][6].='(第11节)'.$string;
                break;
            case 'M':
                $result[$one['day']][1].=$string;
                $result[$one['day']][2].=$string;
                break;
            case 'N':
                $result[$one['day']][3].=$string;
                $result[$one['day']][4].=$string;
                break;
            case 'P':
                $result[$one['day']][5].=$string;
                $result[$one['day']][6].=$string;
                break;
            case 'S':
                $result[$one['day']][1].=$string;
                $result[$one['day']][2].=$string;
                $result[$one['day']][3].=$string;
                $result[$one['day']][4].=$string;
                break;
            case 'Q': //中午
                $result[$one['day']][7].=$string;
                break;
            case 'R': //下午
                $result[$one['day']][8].=$string;
                break;
            default:
                break;
        }
        return $result;
    }

    /**获取教师未排时间地点的课程，返回数组。课名coursename，课号courseno，主修班级classname
     * @param $year
     * @param $term
     * @param $teacherno
     * @return mixed
     */
    private function _getTeacherCourseUnschedule($year,$term,$teacherno){
        $condition=null;
        $condition['teacherplan.teacherno']=$teacherno;
        $condition['scheduleplan.year']=$year;
        $condition['scheduleplan.term']=$term;
        $result=$this->query->table('teacherplan')->join('scheduleplan ',' scheduleplan.recno=teacherplan.map')
            ->join('courseplan ',' courseplan.courseno+courseplan.[group]=scheduleplan.courseno+scheduleplan.[group] and courseplan.year=scheduleplan.year
            and scheduleplan.term=courseplan.term')
            ->join('classes ',' classes.classno=courseplan.classno')
            ->join('courses ',' courses.courseno=courseplan.courseno')
            ->join('schedule ',' schedule.map=teacherplan.recno','LEFT')
            ->field("rtrim(courses.coursename) coursename,scheduleplan.courseno+scheduleplan.[group] courseno,
dbo.GROUP_CONCAT(distinct rtrim(classes.classname),',') classname")
            ->group("courses.coursename,scheduleplan.courseno+scheduleplan.[group]")
            ->where($condition)->where('(schedule.day is null or day =0)')->select();
        return $result;
    }
    /**获取某个教师指定学年学期的课表，二维数组，第一维为星期第二维为节次
     * @param string $year
     * @param string $term
     * @param string $teacherno
     * @return mixed|null
     * @throws \think\Exception
     */
    public function getTeacherTimeTable($year='',$term='',$teacherno=''){
        if($year==''||$term==''||$teacherno=='')
            throw new Exception('year term teacherno is empty ',MyException::PARAM_NOT_CORRECT);

        $condition=null;
        $result=null;
        for($i=0;$i<10;$i++){
            for($j=0;$j<10;$j++)
                $result[$i][$j]='';
        }
        $result[0][0]='';
        $condition['teacherplan.teacherno']=$teacherno;
        $condition['schedule.year']=$year;
        $condition['schedule.term']=$term;
        //读取教师的排课记录。
        $data=$this->query->table('schedule')->join('teacherplan ',' teacherplan.recno=schedule.map')
            ->join('oewoptions ',' oewoptions.code=schedule.oew')
            ->join('classrooms ',' classrooms.roomno=schedule.roomno')
            ->join('courseplan ',' courseplan.courseno+courseplan.[group]=schedule.courseno+schedule.[group] and courseplan.year=schedule.year and courseplan.term=schedule.term')
            ->join('classes ',' classes.classno=courseplan.classno')
            ->join('courses ',' courses.courseno=schedule.courseno')
            ->join('taskoptions ',' taskoptions.code=teacherplan.task')
            ->join('timesections ',' timesections.name=schedule.time')
            ->field("dbo.GROUP_CONCAT(distinct rtrim(classes.classname),'，') as classname,dbo.GROUP_OR(schedule.weeks&oewoptions.timebit2) week,
classrooms.jsn roomname,schedule.courseno+schedule.[group] as courseno,courses.coursename,schedule.day,schedule.time,taskoptions.name taskname,timesections.value as timename")
            ->group('classrooms.jsn,schedule.courseno+schedule.[group],courses.coursename,taskoptions.name,schedule.day,schedule.time,timesections.value')->where($condition)->select();
        foreach($data as $one){
            $string=$one['courseno'].':'.$one['coursename'].'('.$one['roomname'].')<br/>'.$one['classname'].' '.$one['taskname'];
            $common_week=isset($this->oew[$one['week']]);
            $string=$common_week?$this->oew[$one['week']].$string.'<br/>':$string.'<br/>周次:'.implode(' ',str_split(week_dec2bin_reserve($one['week'],20), 4)).'<br/>';
            $result=$this->_buildSingle($result,$one,$string);
        }
        //读取教师未排时间地点的课程
        $unschedule=$this->_getTeacherCourseUnschedule($year,$term,$teacherno);
        if(count($unschedule)>0){
            foreach($unschedule as $one){
                $result[0][0].=$one['courseno'].':'.$one['coursename'].' '.$one['classname'];
            }
        }
        return $result;
    }
    /**获取某个教师指定学年学期的课表，二维数组，第一维为星期第二维为节次(英文版)
     * @param string $year
     * @param string $term
     * @param string $teacherno
     * @return mixed|null
     * @throws \think\Exception
     */
    public function getTeacherTimeTableEnglish($year='',$term='',$teacherno=''){
        if($year==''||$term==''||$teacherno=='')
            throw new Exception('year term teacherno is empty ',MyException::PARAM_NOT_CORRECT);

        $condition=null;
        $result=null;
        for($i=0;$i<10;$i++){
            for($j=0;$j<10;$j++)
                $result[$i][$j]='';
        }
        $result[0][0]='';
        $condition['teacherplan.teacherno']=$teacherno;
        $condition['schedule.year']=$year;
        $condition['schedule.term']=$term;
        //读取教师的排课记录。
        $data=$this->query->table('schedule')->join('teacherplan ',' teacherplan.recno=schedule.map')
            ->join('oewoptions ',' oewoptions.code=schedule.oew')
            ->join('classrooms ',' classrooms.roomno=schedule.roomno')
            ->join('courseplan ',' courseplan.courseno+courseplan.[group]=schedule.courseno+schedule.[group] and courseplan.year=schedule.year and courseplan.term=schedule.term')
            ->join('classes ',' classes.classno=courseplan.classno')
            ->join('courses ',' courses.courseno=schedule.courseno')
            ->join('taskoptions ',' taskoptions.code=teacherplan.task')
            ->join('timesections ',' timesections.name=schedule.time')
            ->field("dbo.GROUP_CONCAT(distinct rtrim(classes.englishname),'，') as englishclass,dbo.GROUP_CONCAT(distinct rtrim(classes.classname),'，') as classname,
            dbo.GROUP_OR(schedule.weeks&oewoptions.timebit2) week,
            classrooms.jsn roomname,schedule.courseno+schedule.[group] as courseno,rtrim(courses.englishname) englishcourse,courses.coursename,schedule.day,schedule.time,taskoptions.name taskname,
            timesections.value as timename")
            ->group('classrooms.jsn,schedule.courseno+schedule.[group],courses.englishname,courses.coursename,taskoptions.name,schedule.day,schedule.time,timesections.value')->where($condition)->select();
        foreach($data as $one){
            $coursename=$one['englishcourse']==""?$one['coursename']:$one['englishcourse'];
            $classname=$one['englishclass']==""?$one['classname']:$one['englishclass'];
            $string=$one['courseno'].':'.$coursename.'('.$one['roomname'].')<br/>'.$classname;
            $common_week=isset($this->oew_english[$one['week']]);
            $string=$common_week?$this->oew_english[$one['week']].$string.'<br/>':$string.'<br/>week:'.implode(' ',str_split(week_dec2bin_reserve($one['week'],20), 4)).'<br/>';
            $result=$this->_buildSingle($result,$one,$string);
        }
        //读取教师未排时间地点的课程
        $unschedule=$this->_getTeacherCourseUnschedule($year,$term,$teacherno);
        if(count($unschedule)>0){
            foreach($unschedule as $one){
                $result[0][0].=$one['courseno'].':'.$one['coursename'].' '.$one['classname'];
            }
        }
        return $result;
    }
    /**获取教室指定学年学期的课表
     * @param string $year
     * @param string $term
     * @param string $roomno 教室号
     * @param bool $all 是否显示借用部分
     * @return mixed|null
     * @throws Exception
     */
    public function getRoomTimeTable($year='',$term='',$roomno='',$all=false){
        if($year==''||$term==''||$roomno=='')
            throw new Exception('year term roomno is empty ',MyException::PARAM_NOT_CORRECT);

        $condition=null;
        $result=null;
        for($i=0;$i<10;$i++){
            for($j=0;$j<10;$j++)
                $result[$i][$j]='';
        }
        $condition['classrooms.roomno']=$roomno;
        $condition['schedule.year']=$year;
        $condition['schedule.term']=$term;
        //读取教室的排课记录。
        $data=$this->query->table('schedule')->join('teacherplan ',' teacherplan.recno=schedule.map')
            ->join('oewoptions ',' oewoptions.code=schedule.oew')
            ->join('classrooms ',' classrooms.roomno=schedule.roomno')
            ->join('courseplan ',' courseplan.courseno+courseplan.[group]=schedule.courseno+schedule.[group] and courseplan.year=schedule.year and courseplan.term=schedule.term')
            ->join('classes ',' classes.classno=courseplan.classno')
            ->join('courses ',' courses.courseno=schedule.courseno')
            ->join('taskoptions ',' taskoptions.code=teacherplan.task')
            ->join('timesections ',' timesections.name=schedule.time')
            ->join('teachers','teachers.teacherno=teacherplan.teacherno')
            ->field("dbo.GROUP_CONCAT(distinct rtrim(classes.classname),'，') as classname,dbo.GROUP_OR(schedule.weeks&oewoptions.timebit2) week,
            rtrim(teachers.name) teachername,schedule.courseno+schedule.[group] as courseno,rtrim(courses.coursename) coursename,schedule.day,
            schedule.time,rtrim(taskoptions.name) taskname,rtrim(timesections.value) as timename")
            ->group('teachers.name,schedule.courseno+schedule.[group],courses.coursename,taskoptions.name,schedule.day,schedule.time,timesections.value')->where($condition)->select();
        foreach($data as $one){
            $string=$one['courseno'].':'.$one['coursename'].'('.$one['teachername'].')'.$one['classname'];
            $common_week=isset($this->oew[$one['week']]);
            $string=$common_week?$this->oew[$one['week']].$string.'<br/>':$string.'<br/>周次:'.implode(' ',str_split(week_dec2bin_reserve($one['week'],20), 4)).'<br/>';
            $result=$this->_buildSingle($result,$one,$string);
        }
        //读取教室的借用记录
        if($all) {
            $condition = null;
            $condition['roomreserve.year'] = $year;
            $condition['roomreserve.term'] = $term;
            $condition['roomreserve.roomno'] = $roomno;
            $condition['roomreserve.approved']=1;
            $data = $this->query->table('roomreserve')->join('schools', 'schools.school=roomreserve.school')
                ->join('timesections', 'timesections.name=roomreserve.time')
                ->field('rtrim(schools.name) schoolname,rtrim(timesections.value) timename,day,roomreserve.time,dbo.GROUP_OR(roomreserve.weeks) as week')
                ->group('schools.name,timesections.value,roomreserve.time,roomreserve.day')
                ->where($condition)->select();
            foreach ($data as $one) {
                $string = $one['schoolname'] . '借用<br/>周次:' . implode(' ', str_split(week_dec2bin_reserve($one['week'], 20), 4)) . '<br/>';
                $result = $this->_buildSingle($result, $one, $string);
            }
        }
        return $result;
    }

    /**获取班级的未排课课程
     * @param $year
     * @param $term
     * @param $classno
     * @return false|\PDOStatement|string|\think\Collection
     */
    private function _getClassCourseUnschedule($year,$term,$classno){
        $condition=null;
        $condition['courseplan.classno']=$classno;
        $condition['courseplan.year']=$year;
        $condition['courseplan.term']=$term;
        $result=$this->query->table('courseplan')
            ->join('scheduleplan ',' courseplan.courseno+courseplan.[group]=scheduleplan.courseno+scheduleplan.[group] and courseplan.year=scheduleplan.year
            and scheduleplan.term=courseplan.term')
            ->join('courses ',' courses.courseno=courseplan.courseno')
            ->join('teacherplan','teacherplan.map=scheduleplan.recno','LEFT')
            ->join('schedule ',' schedule.map=teacherplan.recno','LEFT')
            ->field("rtrim(courses.coursename) coursename,scheduleplan.courseno+scheduleplan.[group] courseno")
            ->group("courses.coursename,scheduleplan.courseno+scheduleplan.[group]")
            ->where($condition)->where('(schedule.day is null or day =0)')->select();
        return $result;
    }
    /**获取班级课表信息
     * @param string $year
     * @param string $term
     * @param string $classno
     * @return mixed|null
     * @throws Exception
     */
    public function getClassTimeTable($year='',$term='',$classno=''){
        if($year==''||$term==''||$classno=='')
            throw new Exception('year term classno is empty ',MyException::PARAM_NOT_CORRECT);

        $condition=null;
        $result=null;
        for($i=0;$i<10;$i++){
            for($j=0;$j<10;$j++)
                $result[$i][$j]='';
        }
        $condition['courseplan.classno']=$classno;
        $condition['courseplan.year']=$year;
        $condition['courseplan.term']=$term;
        //读取教师的排课记录。
        $data=$this->query->table('courseplan')
            ->join('scheduleplan','scheduleplan.courseno=courseplan.courseno and scheduleplan.[group]= courseplan.[group]
            and scheduleplan.year=courseplan.year and scheduleplan.term=courseplan.term')
            ->join('teacherplan ','teacherplan.map=scheduleplan.recno')
            ->join('schedule ',' schedule.map=teacherplan.recno')
            ->join('teachers ',' teachers.teacherno=teacherplan.teacherno')
            ->join('oewoptions ',' oewoptions.code=schedule.oew')
            ->join('classrooms ',' classrooms.roomno=schedule.roomno')
            ->join('courses ',' courses.courseno=schedule.courseno')
            ->join('taskoptions ',' taskoptions.code=teacherplan.task')
            ->join('timesections ',' timesections.name=schedule.time')
            ->field("dbo.GROUP_OR(schedule.weeks&oewoptions.timebit2) week,dbo.GROUP_CONCAT(rtrim(teachers.name),',') teachername,classrooms.jsn roomname,
            schedule.courseno+schedule.[group] as courseno,courses.coursename,schedule.day,schedule.time,taskoptions.name taskname,timesections.value as timename")
            ->group('classrooms.jsn ,schedule.courseno+schedule.[group],courses.coursename,schedule.day,schedule.time,taskoptions.name,timesections.value')
            ->where($condition)->select();
        foreach($data as $one){
            $string=$one['courseno'].':'.$one['coursename'].'('.$one['roomname'].')<br/>'.$one['teachername'].' '.$one['taskname'];
            $common_week=isset($this->oew[$one['week']]);
            $string=$common_week?$this->oew[$one['week']].$string.'<br/>':$string.'<br/>周次:'.implode(' ',str_split(week_dec2bin_reserve($one['week'],20), 4)).'<br/>';
            $result=$this->_buildSingle($result,$one,$string);
        }
        //读取教师未排时间地点的课程
        $unschedule=$this->_getClassCourseUnschedule($year,$term,$classno);
        if(count($unschedule)>0){
            foreach($unschedule as $one){
                $result[0][0].=$one['courseno'].':'.$one['coursename'].',';
            }
        }
        return $result;
    }

    private function _getStudentCourseUnschedule($year,$term,$studentno){
        $condition=null;
        $condition['r32.studentno']=$studentno;
        $condition['r32.year']=$year;
        $condition['r32.term']=$term;
        $result=$this->query->table('r32')
            ->join('scheduleplan ',' r32.courseno+r32.[group]=scheduleplan.courseno+scheduleplan.[group] and r32.year=scheduleplan.year
            and scheduleplan.term=r32.term')
            ->join('courses ',' courses.courseno=r32.courseno')
            ->join('teacherplan','teacherplan.map=scheduleplan.recno','LEFT')
            ->join('schedule ',' schedule.map=teacherplan.recno','LEFT')
            ->field("rtrim(courses.coursename) coursename,scheduleplan.courseno+scheduleplan.[group] courseno")
            ->group("courses.coursename,scheduleplan.courseno+scheduleplan.[group]")
            ->where($condition)->where('(schedule.day is null or day =0)')->select();
        return $result;
    }
    /**获取班级课表信息
     * @param string $year
     * @param string $term
     * @param string $studentno
     * @return mixed|null
     * @throws Exception
     */
    public function getStudentTimeTable($year='',$term='',$studentno=''){
        if($year==''||$term==''||$studentno=='')
            throw new Exception('year term studentno is empty ',MyException::PARAM_NOT_CORRECT);

        $condition=null;
        $result=null;
        for($i=0;$i<10;$i++){
            for($j=0;$j<10;$j++)
                $result[$i][$j]='';
        }
        $condition['r32.studentno']=$studentno;
        $condition['r32.year']=$year;
        $condition['r32.term']=$term;
        //读取教师的排课记录。
        $data=$this->query->table('r32')
            ->join('scheduleplan','scheduleplan.courseno=r32.courseno and scheduleplan.[group]= r32.[group]
            and scheduleplan.year=r32.year and scheduleplan.term=r32.term')
            ->join('teacherplan ','teacherplan.map=scheduleplan.recno')
            ->join('schedule ',' schedule.map=teacherplan.recno')
            ->join('teachers ',' teachers.teacherno=teacherplan.teacherno')
            ->join('oewoptions ',' oewoptions.code=schedule.oew')
            ->join('classrooms ',' classrooms.roomno=schedule.roomno')
            ->join('courses ',' courses.courseno=schedule.courseno')
            ->join('taskoptions ',' taskoptions.code=teacherplan.task')
            ->join('timesections ',' timesections.name=schedule.time')
            ->field("dbo.GROUP_OR(schedule.weeks&oewoptions.timebit2) week,dbo.GROUP_CONCAT(rtrim(teachers.name),',') teachername,classrooms.jsn roomname,
            schedule.courseno+schedule.[group] as courseno,courses.coursename,schedule.day,schedule.time,taskoptions.name taskname,timesections.value as timename")
            ->group('classrooms.jsn ,schedule.courseno+schedule.[group],courses.coursename,schedule.day,schedule.time,taskoptions.name,timesections.value')
            ->where($condition)->select();
        foreach($data as $one){
            $string=$one['courseno'].':'.$one['coursename'].'('.$one['roomname'].')<br/>'.$one['teachername'].' '.$one['taskname'];
            $common_week=isset($this->oew[$one['week']]);
            $string=$common_week?$this->oew[$one['week']].$string.'<br/>':$string.'<br/>周次:'.implode(' ',str_split(week_dec2bin_reserve($one['week'],20), 4)).'<br/>';
            $result=$this->_buildSingle($result,$one,$string);
        }
        //读取教师未排时间地点的课程
        $unschedule=$this->_getStudentCourseUnschedule($year,$term,$studentno);
        if(count($unschedule)>0){
            foreach($unschedule as $one){
                $result[0][0].=$one['courseno'].':'.$one['coursename'].',';
            }
        }
        return $result;
    }

}