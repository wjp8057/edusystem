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


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\vendor\PHPExcel;
use app\common\service\Regdata;
use app\common\service\Register;
use app\common\service\Status;

class Student extends MyController {
    //读取学籍状态
    public function status($page=1,$rows=20)
    {
        try {
            $status=new Status();
            $result = $status->getList($page,$rows);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    //更新学籍状态
    public function updatestatus()
    {
        try {
            $status=new Status();
            $result = $status->update($_POST);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    //获取学生注册信息
    public function registerlist($page=1,$rows=20,$year='',$term='',$studentno='%',$classno='%',$school='',$type=0)
    {
        try {
            $reg=new Regdata();
            $result = $reg->getList($page,$rows,$year,$term,$studentno,$classno,$school,$type);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    //更新学生注册信息
    public function register()
    {
        try {
            $reg=new Regdata();
            $result = $reg->update($_POST);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }

    /**
     * @param int $page
     * @param int $rows
     * @param string $studentno
     * @param string $name
     * @param string $classno
     * @param string $school
     * @param string $status
     * @return \think\response\Json
     */
    public function studentlist($page=1,$rows=20,$studentno='%',$name='%',$classno='%',$school='',$status="")
    {
        try {
            $student=new \app\common\service\Student();
            $result = $student->getList($page,$rows,$studentno,$name,$classno,$school,$status,'');
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }

    /**
     * @param string $studentno
     * @param string $name
     * @param string $classno
     * @param string $school
     * @param string $status
     */
    public function exportbase($studentno='%',$name='%',$classno='%',$school='',$status=''){
        try{
            $student=new \app\common\service\Student();
            $result = $student->getList(1,20000,$studentno,$name,$classno,$school,$status,'');
            $data=$result['rows'];
            $file="学生名单";
            $sheet='全部';
            $title='学生信息列表';
            $template= array("studentno"=>"学号","name"=>"姓名","sexname"=>"性别","classno"=>"班号","classname"=>"班名",
                "schoolname"=>"学院","nationalityname"=>"民族","partyname"=>"政治面貌","majorname"=>"专业","statusname"=>"学籍状态");
            $string=array("studentno","classno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }

    }

    /**
     * @param string $studentno
     * @param string $name
     * @param string $classno
     * @param string $school
     * @param string $status
     */
    public function exportdetail($studentno='%',$name='%',$classno='%',$school='',$status=''){
        try{
            $student=new \app\common\service\Student();
            $result = $student->getDetailList(1,20000,$studentno,$name,$classno,$school,$status);
            $data=$result['rows'];
            $file="学生名单";
            $sheet='全部';
            $title='学生信息列表';
            $template= array("studentno"=>"学号","name"=>"姓名","sexname"=>"性别","years"=>"学制","grade"=>"年级","classno"=>"班号","classname"=>"班名",
                "schoolname"=>"学院","nationalityname"=>"民族","partyname"=>"政治面貌","major"=>"专业代码","majorname"=>"专业",
                "id"=>"身份证号","examno"=>"准考证号","address"=>"家庭地址","tel"=>"联系电话","origin"=>"籍贯","provincename"=>"省份",
                "classcodename"=>"生源类型","statusname"=>"学籍状态");
            $string=array("studentno","classno","id","examno","tel","major");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }

    /**修改指定学生密码
     * @param string $studentno
     * @param string $password
     * @return \think\response\Json
     *
     */
    public function changepassword($studentno='',$password=''){
        try {
            $student=new \app\common\service\Student();
            $result = $student->changePassword($studentno, $password);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }

    /**更新学生信息
     * @param string $op
     * @return \think\response\Json
     */
    public function updatedetail($op="update"){
        try {
            $student=new \app\common\service\Student();
            if($op=="add")
                $result = $student->addStudent($_POST);
            else
                $result = $student->updateDetail($_POST);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }

    /**读取学籍异动信息
     * @param int $page
     * @param int $rows
     * @param string $year
     * @param string $term
     * @param string $studentno
     * @param string $name
     * @param string $infotype
     * @return \think\response\Json
     */
    public function changelist($page=1,$rows=20,$year='',$term='',$studentno='%',$name='%',$infotype="")
    {
        try {
            $register=new Register();
            $result = $register->getList($page,$rows,$year,$term,$studentno,$name,$infotype);
            return json($result);
        } catch (\Exception $e) {
           MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }

    /**
     * 更新学籍异动信息
     */
    public function changeupdate(){
        try {
            $register=new Register();
            $result = $register->update($_POST);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }

    /**导出异动信息
     * @param string $year
     * @param string $term
     * @param string $studentno
     * @param string $name
     * @param string $infotype
     */
    public function exportchange($year='',$term='',$studentno='%',$name='%',$infotype=""){
        try{
            $register=new Register();
            $result = $register->getList(1,10000,$year,$term,$studentno,$name,$infotype);
            $data=$result['rows'];
            $file="学籍异动情况表";
            $sheet='全部';
            $title='学籍异动情况表';
            $template= array("studentno"=>"学号","name"=>"姓名","sexname"=>"性别","classname"=>"班名",
                "schoolname"=>"学院","infotypename"=>"异动类型",
                "fileno"=>"文号","date"=>"发文日期","rem"=>"文件摘要");
            $string=array("studentno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }

} 