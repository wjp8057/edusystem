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
// | Created:2016/11/24 14:38
// +----------------------------------------------------------------------

namespace app\quality\controller;

//学生评教
use app\common\access\Item;
use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\QualityFile;
use app\common\service\QualityStudent;
use app\common\service\QualityStudentDetail;
use app\common\service\Valid;
use app\common\vendor\PHPExcel;

class Student extends MyController {
    //设置学评教开放时间
    public function updatedate($start,$stop)
    {
        $result = null;
        try {
            $obj = new Valid();
            $result = $obj->update($start,$stop,'C');

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //同步课程
    public function syncourse($year,$term,$courseno='%'){
        $result = null;
        try {
            $obj = new QualityStudent();
            $result = $obj->synCourse($year,$term,$courseno);

        } catch (\Exception $e) {
           MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //同步学生
    public function synstudent($year,$term,$courseno='%'){
        $result = null;
        try {
            $obj = new QualityStudentDetail();
            $result = $obj->synStudent($year,$term,$courseno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //获取课程列表
    public function courselist($page=1,$rows=20,$year,$term,$courseno='%',$coursename='%',$teachername='%',$school='',$type='',$enabled='')
    {
        $result=null;
        try {
            $obj=new QualityStudent();
            $result =  $obj->getList($page,$rows,$year,$term,$courseno,$coursename,$teachername,$school,$type,$enabled);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

    //更新课程信息
    public function courseupdate(){
        $result=null;
        try {
            $obj=new QualityStudent();
            $result =  $obj->update($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //检索学生
    public function studentsearch($page=1,$rows=50,$studentno='%',$name='%',$classno='%',$school='',$courseno='',$year='',$term=''){
        $result=null;
        try {
            if($courseno=='') {
                $student = new \app\common\service\Student();
                $result = $student->getList($page, $rows, $studentno, $name, $classno, $school);
            }
            else {
                $obj=new QualityStudentDetail();
                $result=$obj->getStudentList($page,$rows,0,$year,$term,$courseno);
                $data=$result['rows'];
                $amount=count($data);
                for($i=0;$i<$amount;$i++)
                    $data[$i]['name']=$data[$i]['studentname'];
                $result['rows']=$data;
            }
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    public function studentlist($page=1,$rows=20,$id)
    {
        $result=null;
        try {
            $obj=new QualityStudentDetail();
            $result =  $obj->getStudentList($page,$rows,$id);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

    public function studentupdate(){
        $result=null;
        try {
            $obj=new  QualityStudentDetail();
            $result =  $obj->update($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    public function scorelist($page=1,$rows=20,$year,$term,$courseno='%',$coursename='%',$teachername='%',$school='',$type='',$enabled='',$valid='')
    {
        $result=null;
        try {
            $obj=new QualityStudent();
            $result =  $obj->getList($page,$rows,$year,$term,$courseno,$coursename,$teachername,$school,$type,$enabled,$valid);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

    public function scoredetail($page=1,$rows=20,$id)
    {
        $result=null;
        try {
            $obj=new QualityStudentDetail();
            $result =  $obj->getStudentList($page,$rows,$id);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //导出成绩列表
    public function exportscore($year,$term,$courseno='%',$coursename='%',$teachername='%',$school='',$type='',$enabled='',$valid='')
    {
        try{
            $obj=new QualityStudent();
            $result =  $obj->getList(1,5000,$year,$term,$courseno,$coursename,$teachername,$school,$type,$enabled,$valid);
            $data = $result['rows'];
            $length=count($data);
            for($i=0;$i<$length;$i++){
                $data[$i]['enabled']=$data[$i]['done']==1?'是':'否';
                $data[$i]['valid']=$data[$i]['used']==1?'是':'否';
            }
            $file = $year."学年第".$term."学期学评教课程得分表";
            $sheet = '全部';
            $title =$file;
            $template = array('courseno'=>'课号',"coursename" => "课名", "teacherno" => "教师号", "teachername" => "姓名", "schoolname" => "开课学院",'one'=>'态度',
                'two'=>'内容','three'=>'效果','four'=>'方法','score'=>'总分','amount'=>'总人数','done'=>'参加','used'=>'计分','enabled'=>'参加','valid'=>'有效性');
            $string = array("courseno","teacherno");
            $array[] = array("sheet" => $sheet, "title" => $title, "template" => $template, "data" => $data, "string" => $string);
            PHPExcel::export2Excel($file, $array);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    //导出详细打分
    public function exportdetail($id)
    {
        try{
            $obj=new QualityStudentDetail();
            $result =  $obj->getStudentList(1,1000,$id);
            $item=Item::getQualityStudentItem($id);
            $data = $result['rows'];
            $length=count($data);
            for($i=0;$i<$length;$i++){
                $data[$i]['done']=$data[$i]['done']==1?'是':'否';
                $data[$i]['used']=$data[$i]['used']==1?'是':'否';
            }
            $file = $item['year']."学年第".$item['term']."学期学评教打分详细表".$item['courseno'];
            $sheet = '全部';
            $title = $item['year']."学年第".$item['term']."学期学评教打分详细表";
            $subtitle="课号：".$item['courseno']." 课名：".$item['coursename']." 教师：".$item['teachername']." 类型：".$item['typename'];
            $template = array('studentno'=>'学号',"studentname" => "姓名", "classname" => "班级", "schoolname" => "学院", "rank" => "排序",'one'=>'态度',
                'two'=>'内容','three'=>'效果','four'=>'方法','total'=>'总分','done'=>'参加','used'=>'计分');
            $string = array("studentno");
            $array[] = array("sheet" => $sheet, "title" => $title, "template" => $template, "data" => $data, "string" => $string,'subtitle'=>$subtitle);
            PHPExcel::export2Excel($file, $array);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }

    public function filequery($page=1,$rows=20,$year='',$term='',$teacherno='%',$courseno='%',$coursename='%',$teachername='%')
    {
        $result=null;
        try {
            $obj=new QualityFile();
            $result =  $obj->getList($page,$rows,$teacherno,$year,$term,$courseno,$coursename,$teachername);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
}