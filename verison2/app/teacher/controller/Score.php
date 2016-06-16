<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:42
 */

namespace app\teacher\controller;


use app\common\access\MyController;
use app\common\access\MyAccess;
use app\common\service\ViewTeacherCourse;

class Score  extends MyController{
    /*
        * 显示当前登录账户本人的成绩输入
        */
    public function course($page = 1, $rows = 20,$year='2014',$term='1')
    {
        try {
            $course=new ViewTeacherCourse();
            $teacherno= session('S_TEACHERNO');
            $result=$course->getCourseList($page,$rows,$year,$term,$teacherno);
             return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    /*
     * 读取课程成绩单中的相关学生列表
     */
    public function query($page=1,$rows=20,$year='',$term='',$courseno=''){
        try{
            $score=new \app\common\service\Score();
            $result=$score->getStudentList($page,$rows,$year,$term,$courseno);
             return json($result);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    /*
     * 更新课程学生成绩
     */
    public function update(){
        try {
            $score=new \app\common\service\Score();
            $result=$score->updateScore($_POST);//无法用I('post.')获取二维数组
             return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
}