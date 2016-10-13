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

class Schedule extends MyService {
    private $oew=array(262143=>'',87381=>'(单周)',174762=>'(双周)');

    /**将单条课程信息放入课表数组
     * @param $result
     * @param $one
     * @param $string
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
            and courseplan.term=courseplan.term')
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

}