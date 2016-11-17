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
use app\common\service\ViewSchedule;
use app\common\service\R32;

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
            $schedule=new ViewSchedule();
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
            $r32->exportCheckInByCourseno($year,$term,$courseno);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }

    public function exportall($year,$term){
        try{
            $r32=new R32();
            $teacherno= session('S_TEACHERNO');
            $r32->exportCheckInByTeacherno($year,$term,$teacherno);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
}