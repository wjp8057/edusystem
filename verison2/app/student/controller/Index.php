<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:41
 */

namespace app\student\controller;


use app\common\access\Template;
use app\common\access\MyAccess;
use app\common\service\Action;
use app\common\service\Graduate;
use app\common\service\Student;
use app\common\service\Studentplan;

class Index extends Template
{
    /*
   * 教师个人信息页面首页
   */
    public function index()
    {
        try {
            $obj = new Action();
            $menuJson = array('menus' => $obj->getStudentAccessMenu(session('S_USER_NAME'), 1486));
            $this->assign('menu', json_encode($menuJson));
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }
    //学生基础信息
    public function base()
    {
        $student = null;
            $obj = new Student();
            $student = $obj->getStudentDetail(session('S_USER_NAME'));
        $this->assign('student', $student);
        return $this->fetch();
    }
    //学业预警
    public function warn(){
        $student=new Studentplan();
        $result=$student->getStudentList(1,1,session('S_USER_NAME'),'%','%','')['rows'];
        $obj=new Graduate();
        $result=$obj->printByStudentNo($result[0]['majorplanid'],session('S_USER_NAME'));
        $this->assign('result',$result);
        return $this->fetch();
    }

}