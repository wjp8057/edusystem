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
// | Created:2016/11/19 11:24
// +----------------------------------------------------------------------

namespace app\student\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\AddCredit;
use app\common\service\Sport;
use app\common\service\ViewGradeScore;

class Score extends MyController{
    //课程成绩
    public function course($page=1,$rows=20,$year='',$term=''){
        try {
            $obj=new \app\common\service\Score();
            $studentno=session('S_USER_NAME');
            return $obj->getScoreList($page,$rows,$year,$term,$studentno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    //等级考试成绩
    public function grade($page=1,$rows=20,$year='',$month=''){
        try {
            $obj=new ViewGradeScore();
            $studentno=session('S_USER_NAME');
            return $obj->getList($page,$rows,$year,$month,$studentno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    //创新技能学分
    public function add($page=1,$rows=20,$year='',$term=''){
        try {
            $obj=new AddCredit();
            $studentno=session('S_USER_NAME');
            return $obj->getList($page,$rows,$year,$term,$studentno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
//    学期积点分
    public function point($page=1,$rows=20){
        try {
            $obj=new \app\common\service\Score();
            $studentno=session('S_USER_NAME');
            return $obj->getPointList($page,$rows,$studentno);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    public function sport($page=1,$rows=20){
        try {
            $obj=new Sport();
            $studentno=session('S_USER_NAME');
            return $obj->getList($page,$rows,$studentno);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
}