<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:41
 */

namespace app\exam\controller;


use app\common\access\Item;
use app\common\access\Template;
use app\common\access\MyAccess;
use app\common\service\Action;
use app\common\service\R32;
use app\common\service\Schedule;
use app\common\service\TestCourse;
use app\common\service\TestPlan;

class Index extends Template
{
    /*
   * 教师个人信息页面首页
   */
    public function index()
    {
        try {
            $obj = new Action();
            $menuJson = array('menus' => $obj->getUserAccessMenu(session('S_USER_NAME'), 1479));
            $this->assign('menu', json_encode($menuJson));
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }
    //场次时间设置
    public function flagtime($year,$term,$type)
    {
        $this->assign('type',$type);
        $this->assign('year',$year);
        $this->assign('term',$term);
        return $this->fetch();
    }
    //期末自动排考
    public function finalauto(){
        $type=['type'=>'A','typename'=>TestCourse::getTypeName('A')];
        $this->assign('type',$type);
        return $this->fetch('auto');
    }
    //期末考场安排
    public function finalroom(){
        $type=['type'=>'A','typename'=>TestCourse::getTypeName('A')];
        $this->assign('type',$type);
        return $this->fetch('room');
    }
    //期末监考安排
    public function finalteacher(){
        $type=['type'=>'A','typename'=>TestCourse::getTypeName('A')];
        $this->assign('type',$type);
        return $this->fetch('teacher');
    }
    //期末结果查询
    public function finalquery(){
        $type=['type'=>'A','typename'=>TestCourse::getTypeName('A')];
        $this->assign('type',$type);
        return $this->fetch('schedule');
    }
    //教室时间表
    function roomtimetable($year = '', $term = '', $roomno = '')
    {
        try {
            $title['year'] = $year;
            $title['term'] = $term;
            $title['time'] = date("Y-m-d H:i:s");
            $title['name']=Item::getRoomItem($roomno)['name'];
            $this->assign('title', $title);
            $schedule = new Schedule();
            $time = $schedule->getRoomTimeTable($year, $term, $roomno);
            $this->assign('time', $time);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch('all@index/timetable');
    }
    //打印座位表
    function seat($year, $term, $courseno,$type='A', $page = 1)
    {
        try {
            //头部信息

            $obj=new R32();
            $result=$obj->getStudentList($page,120,$year,$term,$courseno)['rows'];
            $amount = count($result);
            //给一个默认的座位
            for($i=0;$i<$amount;$i++)
                $result[$i]['seat']=$i+1;
            //每一个开始都随机换一个位置

            for($i=0;$i<$amount;$i++){
                $temp=$result[$i]['seat'];
                $seat=rand(1,$amount-1);
                $result[$i]['seat']=$result[$seat]['seat'];
                $result[$seat]['seat']=$temp;
            }
            $obj2=new TestPlan();
            $course=$obj2->getList(1,1,$year,$term,$type,'','','','%','%',$courseno)['rows'][0];
            $course['teachername']=$course['teachername1'].','.$course['teachername2'].$course['teachername3'];
            $course['year']=$year;
            $course['term']=$term;
            $course['typename']=TestCourse::getTypeName($type);
            $course['roomname']=$course['roomno1'];
            $this->assign('course', $course);
            $seatstring = '';
            for ($i = 0; $i < 40; $i++) {
                $seatstring .= '<tr>';
                $seatstring .= $i < $amount ? '<td>' . $result[$i]["studentno"] . '</td><td>' . $result[$i]["studentname"] . '</td><td>' . $result[$i]["seat"] . '</td>' : '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $seatstring .= ($i + 40) < $amount ? '<td>' . $result[$i + 40]["studentno"] . '</td><td>' . $result[$i + 40]["studentname"] . '</td><td>' . $result[$i + 40]["seat"] . '</td>' : '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $seatstring .= ($i + 80) < $amount ? '<td>' . $result[$i + 80]["studentno"] . '</td><td>' . $result[$i + 80]["studentname"] . '</td><td>' . $result[$i + 80]["seat"] . '</td>' : '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $seatstring .= '</tr>';
            }
            $this->assign('seat', $seatstring);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch('seat');

    }

    //期初自动排考
    public function startauto(){
        $type=['type'=>'B','typename'=>TestCourse::getTypeName('B')];
        $this->assign('type',$type);
        return $this->fetch('auto');
    }
    //期初考场安排
    public function startroom(){
        $type=['type'=>'B','typename'=>TestCourse::getTypeName('B')];
        $this->assign('type',$type);
        return $this->fetch('room');
    }
    //期初监考安排
    public function startteacher(){
        $type=['type'=>'B','typename'=>TestCourse::getTypeName('B')];
        $this->assign('type',$type);
        return $this->fetch('teacher');
    }
    //期初结果查询
    public function startquery(){
        $type=['type'=>'B','typename'=>TestCourse::getTypeName('B')];
        $this->assign('type',$type);
        return $this->fetch('schedule');
    }

    //毕业自动排考
    public function graduateauto(){
        $type=['type'=>'C','typename'=>TestCourse::getTypeName('C')];
        $this->assign('type',$type);
        return $this->fetch('auto');
    }
    //毕业考场安排
    public function graduateroom(){
        $type=['type'=>'C','typename'=>TestCourse::getTypeName('C')];
        $this->assign('type',$type);
        return $this->fetch('room');
    }
    //毕业监考安排
    public function graduateteacher(){
        $type=['type'=>'C','typename'=>TestCourse::getTypeName('C')];
        $this->assign('type',$type);
        return $this->fetch('teacher');
    }
    //毕业结果查询
    public function graduatequery(){
        $type=['type'=>'C','typename'=>TestCourse::getTypeName('C')];
        $this->assign('type',$type);
        return $this->fetch('schedule');
    }
}