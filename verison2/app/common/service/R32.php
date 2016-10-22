<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:48
 */

namespace app\common\service;


use app\common\access\MyException;
use app\common\access\MyService;
use think\Exception;
use app\common\vendor\PHPExcel;

/**选课记录
 * Class R32
 * @package app\common\service
 */
class R32 extends  MyService {
    /**获取课程选修学生列表
     * @param int $page
     * @param int $rows
     * @param string $year
     * @param string $term
     * @param string $courseno
     * @return array|null
     * @throws \think\Exception
     */
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

    /**根据课号导出考勤表
     * @param $year
     * @param $term
     * @param $courseno
     * @throws Exception
     */
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

    /**根据教师号导出考勤表
     * @param $year
     * @param $term
     * @param $teacherno
     * @throws Exception
     */
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
}