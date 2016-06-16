<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:41
 */

namespace app\teacher\controller;


use app\common\access\Template;
use app\common\access\MyAccess;
use app\common\service\Action;
use app\common\service\Course;
use app\common\service\R32;
use app\common\service\Schedule;
use app\common\service\Score;
use app\common\service\ViewScheduleTable;

class Index extends  Template{
    /*
   * 教师个人信息页面首页
   */
    public function index(){
        try{
            $obj=new Action();
            $menuJson=array('menus'=>$obj->getUserAccessMenu(session('S_USER_NAME'),264));
            $this->assign('menu',json_encode($menuJson));
            return $this->fetch();
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    /*
     * 成绩输入页面-课程成绩输入
     */
    function teacherinput($year='',$term='',$courseno='',$type=''){
        try{
            $scoreType='未知';
            switch($type){
                case 't':$scoreType='两级制';
                    break;
                case 'f':$scoreType='五级制';
                    break;
                case 'h':$scoreType='百分制';
                    break;
                default:break;
            }
            $title=$year.'学年第'.$term.'学期期末成绩输入('.$scoreType.')';
            $this->assign('title',$title);
            $course=new Course();
            $coursename= $course->getNameByCourseNo(substr($courseno,0,7));
            $course='课号：'.$courseno.' 课名：'.$coursename;
            $this->assign('course',$course);
            $score=new Score();
            $examdate=$score->getCourseExamDate($year,$term,$courseno);
            $this->assign('examdate',$examdate);
            return $this->fetch();
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    /*
     * 成绩输入-打印成绩单
     */
    function printscore($year='',$term='',$courseno='',$page=1){
        try{
            //头部信息
            $this->assign('year',$year);
            $this->assign('term',$term);
            $schedule=new ViewScheduleTable();
            $course=$schedule->getCourseInfo($year,$term,$courseno);
            $this->assign('course',$course);
            //成绩信息
            $score=new Score();
            $student=$score->getStudentList($page,120,$year,$term,$courseno);
            $result=$student['rows'];
            $amount=count($result);//当前页行数
            $scorestring='';
            for($i=0;$i<40;$i++){
                $scorestring.='<tr>';
                $scorestring.=$i<$amount?'<td>'.$result[$i]["studentno"].'</td><td>'.$result[$i]["name"].'</td><td>'.$result[$i]["printscore"].'</td>':'<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $scorestring.=($i+40)<$amount?'<td>'.$result[$i+40]["studentno"].'</td><td>'.$result[$i+40]["name"].'</td><td>'.$result[$i+40]["printscore"].'</td>':'<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $scorestring.=($i+80)<$amount?'<td>'.$result[$i+80]["studentno"].'</td><td>'.$result[$i+80]["name"].'</td><td>'.$result[$i+80]["printscore"].'</td>':'<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $scorestring.='</tr>';
            }
            $this->assign('score',$scorestring);

            $summary=$score->getCoursePercent($year,$term,$courseno);
            $this->assign('summary',$summary);
            return $this->fetch();
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }

    }
    /*
     * 课程学生信息页面
     */
    function student($year='',$term='',$courseno='',$page=1){
        try{
            //头部信息
            $this->assign('year',$year);
            $this->assign('term',$term);
            $schedule=new ViewScheduleTable();
            $course=$schedule->getCourseInfo($year,$term,$courseno);

            $this->assign('course',$course);
            //学生名单
            $r32=new R32();
            $student=$r32->getStudentList($page,45,$year,$term,$courseno);
            $result=$student['rows'];
            $amount=count($result);//当前页行数
            $score='';
            for($i=0;$i<45;$i++){
                $score.='<tr>';
                $score.=$i<$amount?'<td>'.$result[$i]["studentno"].'</td><td>'.$result[$i]["studentname"].'</td><td>'.$result[$i]["classname"].'</td>'
                    :'<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                for($j=0;$j<17;$j++)
                    $score.='<td>&nbsp;</td>';
                $score.='</tr>';
            }
            $this->assign('student',$score);
            return $this->fetch();
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    /*
     * 教师课表页面
     */
    function timetable($year='',$term=''){
        try {
            $title['year'] = $year;
            $title['term'] = $term;
            $title['time'] = date("Y-m-d H:i:s");
            $title['teachername'] = session('S_TEACHER_NAME');
            $this->assign('title', $title);
            $teacherno = session("S_TEACHERNO");
            $schedule = new Schedule();
            $time = $schedule->getTeacherTimeTable($year, $term, $teacherno);
            $this->assign('time', $time);
            return $this->fetch();
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
}