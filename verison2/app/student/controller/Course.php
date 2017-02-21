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
// | Created:2016/12/1 14:52
// +----------------------------------------------------------------------

namespace app\student\controller;


use app\common\access\Item;
use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\R32;
use app\common\service\R32Dump;
use app\common\service\ViewScheduleTable;

class Course extends MyController {
    //检索课程
    public function query($page=1,$rows=20,$year,$term,$courseno='%',$classno='%',$coursename='%',$teachername='%',$school='',$weekday='',$time='',$rest=''){
        try {
            $obj=new ViewScheduleTable();
            return $obj->getList($page,$rows,$year,$term,$courseno,$classno,$coursename,$teachername,$school,$weekday,$time,$rest);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    //更新选课记录
    public function update()
    {
        $result = null;
        try {
            $valid=Item::getValidItem('B');
            $valid['now']=date("Y-m-d H:m:s");
            if(strtotime($valid['now'])>strtotime($valid['stop'])||strtotime($valid['now'])<strtotime($valid['start']))
            {
                return json(['info'=>'现在是'.$valid['now'].',不在选课、退课时间内!','status'=>0]);
            }
            $obj = new R32();
            $result = $obj->selectByStudent($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //已选课程
    public function selected($page=1,$rows=20,$year,$term){
        try {
            $studentno=session('S_USER_NAME');
            $obj=new R32();
            return $obj->getSelectedCourse($page,$rows,$year,$term,$studentno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    //被筛选记录
    public function filter($page=1,$rows=20,$year,$term){
        try {
            $studentno=session('S_USER_NAME');
            $obj=new R32Dump();
            return $obj->getList($page,$rows,$year,$term,$studentno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
}