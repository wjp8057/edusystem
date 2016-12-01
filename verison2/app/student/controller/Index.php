<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:41
 */

namespace app\student\controller;


use app\common\access\Item;
use app\common\access\MyException;
use app\common\access\Template;
use app\common\access\MyAccess;
use app\common\service\Action;
use app\common\service\Graduate;
use app\common\service\QualityStudentDetail;
use app\common\service\Schedule;
use app\common\service\Student;
use app\common\service\Studentplan;
use think\Exception;
use think\Request;

class Index extends Template
{
    /*
   * 教师个人信息页面首页
   */
    public function index()
    {
        try {
            $obj = new Action();
            $menuJson = array('menus' => $obj->getStudentAccessMenu(session('S_USER_NAME'), 1486));
            $this->assign('menu', json_encode($menuJson));
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }
    //学生基础信息
    public function base()
    {
        $student = null;
            $obj = new Student();
            $student = $obj->getStudentDetail(session('S_USER_NAME'));
        $this->assign('student', $student);
        return $this->fetch();
    }
    //学业预警
    public function warn(){
        $student=new Studentplan();
        $result=$student->getStudentList(1,1,session('S_USER_NAME'),'%','%','')['rows'];
        $obj=new Graduate();
        $result=$obj->printByStudentNo($result[0]['majorplanid'],session('S_USER_NAME'));
        $this->assign('result',$result);
        return $this->fetch();
    }
    //班级课表
    function classtable($year='', $term='')
    {
        try {
            $year=$year==''?get_year_term()['year']:$year;
            $term=$term==''?get_year_term()['term']:$term;
            $classno = session('S_DEPART_NO');
            $title['year'] = $year;
            $title['term'] = $term;
            $title['time'] = date("Y-m-d H:i:s");
            $title['name'] = session('S_DEPART_NAME');
            $this->assign('title', $title);
            $schedule = new Schedule();
            $time = $schedule->getClassTimeTable($year, $term, $classno);
            $this->assign('time', $time);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch('all@index/timetable');
    }
    //个人课表
    function mytable($year='', $term='')
    {
        try {
            $year=$year==''?get_year_term()['year']:$year;
            $term=$term==''?get_year_term()['term']:$term;
            $studentno = session('S_USER_NAME');
            $title['year'] = $year;
            $title['term'] = $term;
            $title['time'] = date("Y-m-d H:i:s");
            $title['name'] = session('S_REAL_NAME');
            $this->assign('title', $title);
            $schedule = new Schedule();
            $time = $schedule->getStudentTimeTable($year, $term, $studentno);
            $this->assign('time', $time);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch('all@index/timetable');
    }
    //学分认定申请
    function creditapply()
    {
        try {
            $valid=Item::getValidItem('A');
            $valid['now']=date("Y-m-d H:m:s");
            $this->assign('valid', $valid);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }
    //学评教首页
    function quality()
    {
        try {
            $valid=Item::getValidItem('C');
            $valid['now']=date("Y-m-d H:m:s");
            $this->assign('valid', $valid);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }
    //学评教汇总表
    //学评教打分表
    function qualitycourse($year='',$term='',$id=0)
    {
        try {
            $valid=Item::getValidItem('C');
            $valid['now']=date("Y-m-d H:m:s");
            if(strtotime($valid['now'])>strtotime($valid['stop'])||strtotime($valid['now'])<strtotime($valid['start']))
            {
                echo '现在是'.$valid['now'].'不在学评教时间内:'.$valid['start'].'-'.$valid['stop'];
                die();
            }
            $studentno=session('S_USER_NAME');
            $yearterm=get_year_term();
            if($year=='') $year=$yearterm['year'];
            if($term=='') $term=$yearterm['term'];
            if($id==0)
                $id=QualityStudentDetail::getNextID($year,$term,$id,$studentno);
            //如果没有考评条目了，转回首页
            if($id==0) {
                $request =Request::instance();
                header('Location:' . $request->root().'/student/index/quality');
                exit();
            }
            $obj=new QualityStudentDetail();
            $result=$obj->getList(1,1,$year,$term,$studentno,$id);
            $result=$result['rows'][0];
            $result['nextid']=QualityStudentDetail::getNextID($year,$term,$id,$studentno);
            $this->assign('course', $result);
            $template='';
            switch($result['type']){
                case 'A':
                    $template='qualitycoursea';
                    break;
                case 'B':
                    $template='qualitycourseb';
                    break;
                case 'C':
                    $template='qualitycoursec';
                    break;
                case 'D':
                    $template='qualitycoursed';
                    break;
                default:
                    throw new Exception('type'.$result['type'], MyException::PARAM_NOT_CORRECT);
            }
            return $this->fetch($template);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
}