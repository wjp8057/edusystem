<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:41
 */

namespace app\analysis\controller;


use app\common\access\Item;
use app\common\access\Template;
use app\common\access\MyAccess;
use app\common\service\Action;
use app\common\service\Schedule;
use app\common\service\ViewSchedule;
use app\common\service\R32;
use app\common\service\ViewScheduleTable;

class Index extends Template
{
    /**首页
     * @return mixed
     */
    public function index()
    {
        try {
            $obj = new Action();
            $menuJson = array('menus' => $obj->getUserAccessMenu(session('S_USER_NAME'), 1368));
            $this->assign('menu', json_encode($menuJson));
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }

    /** 打印教师课表
     * @param $year
     * @param $term
     * @param $teacherno
     * @return mixed
     */
    function processtable($year, $term,$teacherno)
    {
        try {
            $title['year'] = $year;
            $title['term'] = $term;
            $title['time'] = date("Y-m-d H:i:s");
            $title['teachername'] = Item::getTeacherItem($teacherno)['teachername'];
            $this->assign('title', $title);
            $schedule = new Schedule();
            $time = $schedule->getTeacherTimeTable($year, $term, $teacherno);
            $this->assign('time', $time);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }

    /**教室课程列表
     * @param $year
     * @param $term
     * @param $teacherno
     * @return mixed
     */
    function processtablecourse($year, $term,$teacherno)
    {
        try {
            $this->assign('year', $year);
            $this->assign('term', $term);
            $this->assign('teacherno', $teacherno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }

    /**课程学生列表
     * @param string $year
     * @param string $term
     * @param string $courseno
     * @param int $page
     */
    function processcoursestudent($year='',$term='',$courseno='',$page=1){
        try{
            //头部信息
            $this->assign('year',$year);
            $this->assign('term',$term);
            $schedule=new ViewScheduleTable();
            $course=$schedule->getCourseInfo($year,$term,$courseno);
            $this->assign('course',$course);
            //成绩信息
            $score=new \app\common\service\Score();
            $student=$score->getStudentList($page,120,$year,$term,$courseno);
            $result=$student['rows'];
            $amount=count($result);//当前页行数
            $scorestring='';
            for($i=0;$i<40;$i++){
                $scorestring.='<tr>';
                $scorestring.=$i<$amount?'<td>'.$result[$i]["studentno"].'</td><td>'.$result[$i]["name"].'</td><td>&nbsp;</td>':'<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $scorestring.=($i+40)<$amount?'<td>'.$result[$i+40]["studentno"].'</td><td>'.$result[$i+40]["name"].'</td><td>&nbsp;</td>':'<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $scorestring.=($i+80)<$amount?'<td>'.$result[$i+80]["studentno"].'</td><td>'.$result[$i+80]["name"].'</td><td>&nbsp;</td>':'<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $scorestring.='</tr>';
            }
            $this->assign('score',$scorestring);
            return $this->fetch();
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }

    }
}