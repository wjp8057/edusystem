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
// | Created:2016/11/18 8:41
// +----------------------------------------------------------------------

namespace app\student\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\CreditApply;
use app\common\service\Testplan;

class Vacation extends MyController {
    //读取考试安排
    public function testplan($page=1,$rows=20,$year,$term){
        try {
            $obj=new Testplan();
            $studentno=session('S_USER_NAME');
            return $obj->studentCourseList($page,$rows,$year,$term,$studentno);

        } catch (\Exception $e) {
           MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    //读取认定申请列表
    public function creditlist($page=1,$rows=20,$year,$term){
        try {
            $obj=new CreditApply();
            $studentno=session('S_USER_NAME');
            return $obj->getList($page,$rows,$year,$term,$studentno,'%','','');

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    //更新认定申请
    public function creditupdate()
    {
        $result = null;
        try {
            $obj = new CreditApply();
            $result = $obj->update($_POST);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function creditexport($id){
        try{
            $obj = new CreditApply();
            $obj->exportWord($id);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
}