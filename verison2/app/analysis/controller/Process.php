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

namespace app\analysis\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\Classroom;
use app\common\service\ViewSchedule;
use app\common\service\ViewScheduleTable;
use app\common\service\R32;
use app\common\access\Item;
use app\common\vendor\PHPExcel;

class Process extends MyController
{

    public function  room($page = 1, $rows = 20,$year,$term, $roomno = '%', $name = '%', $building = '%', $area = '', $equipment = '',
                              $school = '', $seatmin = 0, $seatmax = 1000)
    {
        $result=null;
        try {
            $room = new Classroom();
            $result = $room->getUsageRate($page, $rows,$year,$term, $roomno, $name, $building, $area, $equipment, $school, $seatmin, $seatmax);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    /**导出教室利用率
     * @param $year
     * @param $term
     * @param string $roomno
     * @param string $name
     * @param string $building
     * @param string $area
     * @param string $equipment
     * @param string $school
     * @param int $seatmin
     * @param int $seatmax
     * @param int $base
     * @return \think\response\Json
     */
    public function exportroom($year,$term, $roomno = '%', $name = '%', $building = '%', $area = '', $equipment = '',
                          $school = '', $seatmin = 0, $seatmax = 1000,$base=40)
    {
        $result=null;
        try {
            $room = new Classroom();
            $result = $room->getUsageRate(1, 1000,$year,$term, $roomno, $name, $building, $area, $equipment, $school, $seatmin, $seatmax);
            $data = $result['rows'];
            $file = $year."学年第".$term."学期教室利用率（".$base."课时基准)";
            $sheet = '全部';
            $title = $file;
            $length=count($data);
            for($i=0;$i<$length;$i++){
                $data[$i]['rate']= $data[$i]['used']*100/40;
            }
            $template = array("roomno" => "教室号", "no" => "房间号","roomname"=>"名称", "building" => "楼名", "areaname" => "校区","seats"=>"座位数",
                "equipmentname"=>"设施", "used"=>"周课时","rate"=>"利用率");
            $string = array("roomno,no");
            $array[] = array("sheet" => $sheet, "title" => $title, "template" => $template, "data" => $data, "string" => $string);
            PHPExcel::export2Excel($file, $array);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    /**教师课程列表
     * @param int $page
     * @param int $rows
     * @param $year
     * @param $term
     * @param $teacherno
     * @return \think\response\Json
     */
    public function tablecourse($page=1,$rows=20,$year,$term,$teacherno){
        $result=null;
        try{
            $schedule=new ViewScheduleTable();
            $result=$schedule->getTeacherCourseList($page,$rows,$year,$term,$teacherno);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    /**导出指定课程课堂考勤表
     * @param $year
     * @param $term
     * @param $teacherno
     */
    public function exportcoursestudent($year,$term,$courseno){
        try{
            $file = "考勤表";
            $array=[];
                $r32 = new R32();
                $result = $r32->getStudentList(1, 10000, $year, $term, $courseno);
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
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    /**导出指定教师的所有课程课堂考勤表
     * @param $year
     * @param $term
     * @param $teacherno
     */
    public function exportcoursecheckin($year,$term,$teacherno){
      try{
        $file = "考勤表";
        $array=[];
            $schedule=new ViewScheduleTable();
            $result=$schedule->getTeacherCourseList(1,1000,$year,$term,$teacherno);
            $courserows=$result['rows'];
            foreach($courserows as $one) {
                $courseno=$one['courseno'];
                $r32 = new R32();
                $result = $r32->getStudentList(1, 10000, $year, $term, $courseno);
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
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }



} 