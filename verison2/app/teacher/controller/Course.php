<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:41
 */
namespace app\teacher\controller;
use app\common\access\MyController;
use app\common\access\MyAccess;
use app\common\vendor\PHPExcel;
use app\common\service\R32;
use app\common\service\ViewScheduleTable;

/**课程
 * Class Course
 * @package app\teacher\controller
 */
class Course extends MyController{
    /**
     * @param int $page
     * @param int $rows
     * @param $year
     * @param $term
     * @return \think\response\Json
     */
    public function query($page=1,$rows=20,$year,$term){
        $result=null;
        try{
            $schedule=new ViewScheduleTable();
            $teacherno= session('S_TEACHERNO');
            $result=$schedule->getTeacherCourseList($page,$rows,$year,$term,$teacherno);

        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    /*
     * 导出课程选课名单
     */
    public function export($year,$term,$courseno){
        try{
            $r32=new R32();
            $result=$r32->getStudentList(1,10000,$year,$term,$courseno);
            $data=$result['rows'];
            $file="课程选课名单";
            $course=new \app\common\service\Course();
            $coursename=$course->getNameByCourseNo(substr($courseno,0,7));
            $sheet=$coursename;
            $title=$year.'年第'.$term.'学期'.$coursename.'('.$courseno.') (共'.count($data).'人)';
            $template= array("studentno"=>"学号","studentname"=>"姓名","sex"=>"性别","classname"=>"班级","schoolname"=>"学院","approachname"=>"修课方式");
            $string=array("studentno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
}