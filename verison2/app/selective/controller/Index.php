<?php


namespace app\selective\controller;


use app\common\access\Item;
use app\common\access\Template;
use app\common\access\MyAccess;
use app\common\service\Action;
use app\common\service\R32;
use app\common\service\Schedule;
use app\common\service\SchedulePlan;

class Index extends  Template{
    public function index(){
        try{
            $obj=new Action();
            $menuJson=array('menus'=>$obj->getUserAccessMenu(session('S_USER_NAME'),334));
            $this->assign('menu',json_encode($menuJson));
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }

    public function managefilterstudent($year,$term,$courseno){
        try{
            $result=Item::getSchedulePlanItem($year,$term,$courseno);
            $this->assign('course',$result);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }
    function settingsyn(){
        try {
            $valid=Item::getValidItem('B');
            $this->assign('valid', $valid);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }
    //按学生检索，批量添加学生页
    public function addbystudent($year,$term,$courseno){
        try{
            $result=Item::getSchedulePlanItem($year,$term,$courseno);
            $this->assign('course',$result);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }
    //根据其他课程选课记录批量添加学生
    public function addbycourse($year,$term,$courseno){
        try{
            $result=Item::getSchedulePlanItem($year,$term,$courseno);
            $this->assign('course',$result);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }

    function classtable($year='', $term='',$who)
    {
        try {
            $year=$year==''?get_year_term()['year']:$year;
            $term=$term==''?get_year_term()['term']:$term;
            $title['year'] = $year;
            $title['term'] = $term;
            $title['time'] = date("Y-m-d H:i:s");
            $title['name'] = Item::getClassItem($who)['classname'];
            $this->assign('title', $title);
            $schedule = new Schedule();
            $time = $schedule->getClassTimeTable($year, $term, $who);
            $this->assign('time', $time);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch('all@index/timetable');
    }
    function selectedtable($year='', $term='',$who)
    {
        try {
            $year=$year==''?get_year_term()['year']:$year;
            $term=$term==''?get_year_term()['term']:$term;
            $title['year'] = $year;
            $title['term'] = $term;
            $title['time'] = date("Y-m-d H:i:s");
            $title['name'] = Item::getClassItem($who)['classname'];
            $this->assign('title', $title);
            $schedule = new R32();
            $content = $schedule->selectedTable($year,$term,$who);
            $this->assign('content', $content);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch('all@index/selectedtable');
    }
}