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

namespace app\base\controller;


use app\common\access\Item;
use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\access\MyException;
use app\common\vendor\PHPExcel;
use app\common\service\Student;
use think\Exception;

/**班级管理
 * Class Classes
 * @package app\base\controller
 */
class Classes extends MyController {
    /**获取班级列表
     * @param int $page
     * @param int $rows
     * @param string $classno
     * @param string $classname
     * @param string $school
     * @return \think\response\Json
     */
    public function classlist($page=1,$rows=20,$classno='%',$classname='%',$school='')
    {
        $result=null;
        try {
            $class=new \app\common\service\Classes();
            $result =  $class->getList($page,$rows,$classno,$classname,$school);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

    /**
     * 更新班级信息
     */
    public function classupdate(){
        $result=null;
        try {
            $class=new \app\common\service\Classes();
            $result =  $class->update($_POST);
        } catch (\Exception $e) {
             MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

    /**学生列表
     * @param int $page
     * @param int $rows
     * @param string $classno
     * @return \think\response\Json
     */
    public function studentlist($page=1,$rows=50,$classno=''){
        $result=null;
        try {
            $student=new Student();
            $result =  $student->getList($page,$rows,'%','%',$classno);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

    /**检索学生
     * @param int $page
     * @param int $rows
     * @param string $studentno
     * @param string $name
     * @param string $classno
     * @param string $school
     * @return \think\response\Json
     */
    public function searchstudent($page=1,$rows=50,$studentno='%',$name='%',$classno='%',$school=''){
        $result=null;
        try {
            $student=new Student();
            $result = $student->getList($page,$rows,$studentno,$name,$classno,$school);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

    /**添加学生
     * @return \think\response\Json
     */
    public function studentadd(){
        $result=null;
        try {
            $class=new \app\common\service\Classes();
            $result =  $class->addStudent($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

    /**导出所有班级学生名单
     * @param string $classno
     * @param string $classname
     * @param string $school
     */
    public function export($classno='%',$classname='%',$school='')
    {
        try{
            $class=new \app\common\service\Classes();
            $result =  $class->getList(1,10000,$classno,$classname,$school);
            $classrows=$result['rows'];
            if(count($classrows)>20){
                throw new Exception('classes amount to export is more than 20',MyException::PARAM_NOT_CORRECT);
            }
            $file="班级学生名单";
            $student=new Student();
            $array=[];
            $amount=0;
            foreach($classrows as $one){
                if($one['amount']>0) { //仅导出有学生的班级
                    $amount++;
                    $result = $student->getList(1, 10000, '%', '%', $one['classno'], '', '','');
                    $class = Item::getClassItem($one['classno'],true);
                    $classname = $class['classname'];
                    $data = $result['rows'];
                    $sheet = str_replace("*", "", $classname);
                    $title = $classname . '学生名单';
                    $template = array("studentno" => "学号", "name" => "姓名", "sexname" => "性别", "statusname" => "学籍状态", 'rem' => '备注');
                    $string = array("studentno");
                    $array[] = array("sheet" => $sheet, "title" => $title, "template" => $template, "data" => $data, "string" => $string);
                }
            }
            if($amount<=0){
                throw new Exception('all classes have no student!',MyException::ITEM_NOT_EXISTS);
            }
            PHPExcel::export2Excel($file,$array);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    //导出班级信息
    public function exportclass($classno='%',$classname='%',$school='')
    {
        try{
            $class=new \app\common\service\Classes();
            $result =  $class->getList(1,10000,$classno,$classname,$school);
            $data = $result['rows'];
            $file = "班级列表";
            $sheet = '全部';
            $title = '';
            $template = array("classno" => "班号", "classname" => "班名","students"=>"预计人数","amount"=>"实际人数","schoolname"=>"学院",
                "major"=>"专业代码","majorname"=>"专业","direction"=>"专业方向代码","directionname"=>"专业方向名称");
            $string = array("classno");
            $array[] = array("sheet" => $sheet, "title" => $title, "template" => $template, "data" => $data, "string" => $string);
            PHPExcel::export2Excel($file, $array);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
} 