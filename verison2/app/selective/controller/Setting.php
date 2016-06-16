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
// | Created:2016/6/15 9:54
// +----------------------------------------------------------------------

namespace app\selective\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\SchedulePlan;
use app\common\service\Student;
use app\common\service\ViewScheduleTable;

class Setting  extends MyController{
    public  function  courselist($page=1,$rows=20,$year,$term,$courseno='%',$coursename='%',$classno='%',$school='',$halflock='',$lock=''){
        try {
            $obj=new SchedulePlan();
            $condition=null;
            if($halflock!='') $condition['scheduleplan.halflock']=$halflock;
            if($lock!='') $condition['scheduleplan.lock']=$lock;
            $result =$obj->getList($page,$rows,$year,$term,$courseno,$coursename,$classno,$school,$condition);
            return json($result);
        } catch (\Exception $e) {
           MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    public function courseupdate(){
        try {
            $obj=new SchedulePlan();
            $result =$obj->updateStatus($_POST);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    public function updateallstatus($year,$term,$halflock,$lock){
        try {
            $obj=new SchedulePlan();
            $result =$obj->updateAllStatus($year,$term,$halflock,$lock);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    public function studentlist($page=1,$rows=20,$studentno='%',$name='%',$school='',$free=''){
        try {
            $obj=new Student();
            $result =$obj->getList($page,$rows,$studentno,$name,'%',$school,'',$free);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    public function studentupdate(){
        try {
            $obj=new Student();
            $result =$obj->updateStatus($_POST);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    public function synscheduletable($year,$term){
        try {
            $obj=new ViewScheduleTable();
            $result =$obj->update($year,$term);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
}