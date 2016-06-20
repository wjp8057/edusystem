<?php


namespace app\selective\controller;


use app\common\access\Item;
use app\common\access\Template;
use app\common\access\MyAccess;
use app\common\service\Action;
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
      /*  try{*/
            $result=Item::getSchedulePlanItem($year,$term,$courseno);
            $this->assign('course',$result);
       /* }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }*/
        return $this->fetch();
    }

}