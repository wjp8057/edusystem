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
// | Created:2016/11/21 14:42
// +----------------------------------------------------------------------

namespace app\score\controller;


use app\common\access\Item;
use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\CreditApply;
use app\common\service\Project;
use app\common\service\ProjectDetail;
use app\common\service\Student;
use app\common\service\Valid;
use app\common\vendor\PHPExcel;

class Credit extends MyController {
    //设置学分认定时间
    public function updatedate($start,$stop)
    {
        $result = null;
        try {
            $obj = new Valid();
            $result = $obj->update($start,$stop,'A');

        } catch (\Exception $e) {
           MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //读取创新技能项目列表
    public function creativelist($page = 1, $rows = 20, $year, $term, $type='A', $school = '')
    {
        $result = null;
        try {
            $obj = new Project();
            $result = $obj->getList($page, $rows, $year, $term, $type, $school);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //更新创新技能项目信息
    public function creativeprojectupdate()
    {
        $result = null;
        try {
            $obj = new Project();
            $result = $obj->update($_POST);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //搜索学生
    public function creativesearch($page=1,$rows=50,$studentno='%',$name='%',$classno='%',$school=''){
        $result=null;
        try {
            $student=new Student();
            $result = $student->getList($page,$rows,$studentno,$name,$classno,$school);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //读取按创新技能项目申报的学生名单
    public function creativestudent($page = 1, $rows = 20, $map)
    {
        $result = null;
        try {
            $obj = new ProjectDetail();
            $result = $obj->getList($page, $rows,'A',$map);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //更新按项目申报的学生名单
    public function creativeupdate()
    {
        $result = null;
        try {
            $obj = new ProjectDetail();
            $result = $obj->update($_POST);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //导出创新技能认定表
    public function exportbatchcreative($id){

        $result=null;
        try {
            $obj=new ProjectDetail();
            $name=Item::getProjectItem($id)['name'];
            $result=$obj->getList(1,10000,'',$id);
            $file=$name."创新技能认定汇总表（统报）";
            $data=$result['rows'];
            $sheet='全部';
            $title=$name;
            $template= array("year"=>"学年","term"=>"学期","studentno"=>"学号","studentname"=>"姓名","classname"=>"班级","schoolname"=>"学院",
                "reason"=>"申请理由", "credit"=>"学分","typename"=>"类型","cerdate"=>"证书时间");
            $string=array("studentno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    //读取创新技能申报学生名单
    public function creativeapply($page = 1, $rows = 20, $year,$term,$studentno='%',$reason='%',$school='',$audit='')
    {
        $result = null;
        try {
            $obj = new CreditApply();
            $result = $obj->getList($page, $rows,$year,$term,$studentno,$reason,$school,'A',$audit);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //审核创新技能申请
    public function creativeaudit()
    {
        $result = null;
        try {
            $obj = new CreditApply();
            $result = $obj->audit($_POST);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //转为素质学分
    public function parsequality($id)
    {
        $result = null;
        try {
            $obj = new CreditApply();
            $result = $obj->parseType($id,'B');

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //导出创新技能认定表
    public function auditexportcreative($year,$term,$studentno='%',$reason='%',$school='',$audit=''){

        $result=null;
        try {
            $obj = new CreditApply();
            $result = $obj->getList(1,10000,$year,$term,$studentno,$reason,$school,'A',$audit);
            $data=$result['rows'];
            $file=$year."学年第".$term."学期创新技能认定汇总表（单个）";
            $sheet='全部';
            $title='';
            $length=count($data);
            for($i=0;$i<$length;$i++){
                $data[$i]['audit']=$data[$i]['audit']==1?'是':'否';
                $data[$i]['verify']=$data[$i]['verify']==1?'是':'否';
            }
            $template= array("year"=>"学年","term"=>"学期","studentno"=>"学号","studentname"=>"姓名","classname"=>"班级","schoolname"=>"学院",
                "reason"=>"申请理由", "credit"=>"学分","typename"=>"类型","cerdate"=>"证书时间","applydate"=>"申请时间","audit"=>"经审核","verify"=>"经认定");
            $string=array("studentno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    //创新学分认定中读取学生列表
    public function creativequery($page = 1, $rows = 20, $year,$term,$studentno='%',$reason='%',$school='',$audit='',$verify='',$batch='0')
    {
        $result = null;
        try {
            if($batch=="0") {
                $obj = new CreditApply();
                $result = $obj->getList($page, $rows, $year, $term, $studentno, $reason, $school, 'A', $audit, $verify);
            }
            else if($batch=="1"){
                $obj=new ProjectDetail();
                $result=$obj->getList($page,$rows,'A','',$year,$term,$reason,$studentno,$school,$verify);
            }
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //提交c创新技能学分认定。
    public function creativeverify($batch)
    {
        $result = null;
        try {
            if($batch=="0") {
                $obj = new CreditApply();
                $result = $obj->verify($_POST);
            }
            else if($batch=="1"){
                $obj=new ProjectDetail();
                $result=$obj->verify($_POST);
            }

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //导出认定界面创新技能认定表
    public function verifyexportcreative($year,$term,$studentno='%',$reason='%',$school='',$audit='',$verify='',$batch){
        $result=null;
        $file='';
        try {
            if($batch=="0") {
                $obj = new CreditApply();
                $result = $obj->getList(1, 10000, $year, $term, $studentno, $reason, $school, 'A', $audit, $verify);
                $file=$year."学年第".$term."学期创新技能认定汇总表（单个）";
            }
            else if($batch=="1") {
                $obj=new ProjectDetail();
                $result=$obj->getList(1,10000,'A','',$year,$term,$reason,$studentno,$school,$verify);
                $file=$year."学年第".$term."学期创新技能认定汇总表（统报）";
            }
            $data=$result['rows'];
            $sheet='全部';
            $title='';
            $length=count($data);
            for($i=0;$i<$length;$i++){
                $data[$i]['audit']=$data[$i]['audit']==1?'是':'否';
                $data[$i]['verify']=$data[$i]['verify']==1?'是':'否';
            }
            $template= array("year"=>"学年","term"=>"学期","studentno"=>"学号","studentname"=>"姓名","classname"=>"班级","schoolname"=>"学院",
                "reason"=>"申请理由", "credit"=>"学分","typename"=>"类型","cerdate"=>"证书时间","applydate"=>"申请时间","audit"=>"经审核","verify"=>"经认定");
            $string=array("studentno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    //读取素质学分项目列表
    public function qualitylist($page = 1, $rows = 20, $year, $term, $type='B', $school = '')
    {
        $result = null;
        try {
            $obj = new Project();
            $result = $obj->getList($page, $rows, $year, $term, $type, $school);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //更新素质学分项目信息
    public function qualityprojectupdate()
    {
        $result = null;
        try {
            $obj = new Project();
            $result = $obj->update($_POST);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //素质学分学生检索
    public function qualitysearch($page=1,$rows=50,$studentno='%',$name='%',$classno='%',$school=''){
        $result=null;
        try {
            $student=new Student();
            $result = $student->getList($page,$rows,$studentno,$name,$classno,$school);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //素质学分项目学生信息
    public function qualitystudent($page = 1, $rows = 20, $map)
    {
        $result = null;
        try {
            $obj = new ProjectDetail();
            $result = $obj->getList($page, $rows,'B',$map);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //更新素质学分参加学生信息
    public function qualityupdate()
    {
        $result = null;
        try {
            $obj = new ProjectDetail();
            $result = $obj->update($_POST);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //导出素质学分认定的表
    public function exportbatchquality($id){
        $result=null;
        try {
            $obj=new ProjectDetail();
            $name=Item::getProjectItem($id)['name'];
            $result=$obj->getList(1,10000,'',$id);
            $file=$name."素质学分认定汇总表（统报）";
            $data=$result['rows'];
            $sheet='全部';
            $title=$name;
            $template= array("year"=>"学年","term"=>"学期","studentno"=>"学号","studentname"=>"姓名","classname"=>"班级","schoolname"=>"学院",
                "reason"=>"申请理由", "credit"=>"学分","typename"=>"类型","cerdate"=>"证书时间");
            $string=array("studentno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    //读取素质学分申报列表
    public function qualityapply($page = 1, $rows = 20, $year,$term,$studentno='%',$reason='%',$school='',$audit='')
    {
        $result = null;
        try {
            $obj = new CreditApply();
            $result = $obj->getList($page, $rows,$year,$term,$studentno,$reason,$school,'B',$audit);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //审核素质学分申请
    public function qualityaudit()
    {
        $result = null;
        try {
            $obj = new CreditApply();
            $result = $obj->audit($_POST);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //转变为创新学分
    public function parsecreative($id)
    {
        $result = null;
        try {
            $obj = new CreditApply();
            $result = $obj->parseType($id,'A');

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //导出单个的素质学分认定汇总表
    public function auditexportquality($year,$term,$studentno='%',$reason='%',$school='',$audit=''){

        $result=null;
        try {
            $obj = new CreditApply();
            $result = $obj->getList(1,10000,$year,$term,$studentno,$reason,$school,'B',$audit);
            $data=$result['rows'];
            $file=$year."学年第".$term."学期素质学分认定汇总表（单个）";
            $sheet='全部';
            $title='';
            $length=count($data);
            for($i=0;$i<$length;$i++){
                $data[$i]['audit']=$data[$i]['audit']==1?'是':'否';
                $data[$i]['verify']=$data[$i]['verify']==1?'是':'否';
            }
            $template= array("year"=>"学年","term"=>"学期","studentno"=>"学号","studentname"=>"姓名","classname"=>"班级","schoolname"=>"学院",
                "reason"=>"申请理由", "credit"=>"学分","typename"=>"类型","cerdate"=>"证书时间","applydate"=>"申请时间","audit"=>"经审核","verify"=>"经认定");
            $string=array("studentno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    //读取素质学分认定信息
    public function qualityquery($page = 1, $rows = 20, $year,$term,$studentno='%',$reason='%',$school='',$audit='',$verify='',$batch='0')
    {
        $result = null;
        try {
            if($batch=="0") {
                $obj = new CreditApply();
                $result = $obj->getList($page, $rows, $year, $term, $studentno, $reason, $school, 'B', $audit, $verify);
            }
            else if($batch=="1"){
                $obj=new ProjectDetail();
                $result=$obj->getList($page,$rows,'B','',$year,$term,$reason,$studentno,$school,$verify);
            }
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //提交素质学分认定
    public function qualityverify($batch)
    {
        $result = null;
        try {
            if($batch=="0") {
                $obj = new CreditApply();
                $result = $obj->verify($_POST);
            }
            else if($batch=="1"){
                $obj=new ProjectDetail();
                $result=$obj->verify($_POST);
            }

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //批量导出素质学分认定
    public function verifyexportquality($year,$term,$studentno='%',$reason='%',$school='',$audit='',$verify='',$batch){
        $result=null;
        $file='';
        try {
            if($batch=="0") {
                $obj = new CreditApply();
                $result = $obj->getList(1, 10000, $year, $term, $studentno, $reason, $school, 'B', $audit, $verify);
                $file=$year."学年第".$term."学期素质学分认定汇总表（单个）";
            }
            else if($batch=="1") {
                $obj=new ProjectDetail();
                $result=$obj->getList(1,10000,'B','',$year,$term,$reason,$studentno,$school,$verify);
                $file=$year."学年第".$term."学期素质学分认定汇总表（统报）";
            }
            $data=$result['rows'];
            $sheet='全部';
            $title='';
            $length=count($data);
            for($i=0;$i<$length;$i++){
                $data[$i]['audit']=$data[$i]['audit']==1?'是':'否';
                $data[$i]['verify']=$data[$i]['verify']==1?'是':'否';
            }
            $template= array("year"=>"学年","term"=>"学期","studentno"=>"学号","studentname"=>"姓名","classname"=>"班级","schoolname"=>"学院",
                "reason"=>"申请理由", "credit"=>"学分","typename"=>"类型","cerdate"=>"证书时间","applydate"=>"申请时间","audit"=>"经审核","verify"=>"经认定");
            $string=array("studentno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    //检索信息
    public function query($page = 1, $rows = 20, $year,$term,$studentno='%',$reason='%',$school='',$audit='',$verify='',$batch='0',$type='')
    {
        $result = null;
        try {
            if($batch=="0") {
                $obj = new CreditApply();
                $result = $obj->getList($page, $rows, $year, $term, $studentno, $reason, $school, $type, $audit, $verify);
            }
            else if($batch=="1"){
                $obj=new ProjectDetail();
                $result=$obj->getList($page,$rows,$type,'',$year,$term,$reason,$studentno,$school,$verify);
            }
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //导出到ecel
    public function export($year,$term,$studentno='%',$reason='%',$school='',$audit='',$verify='',$batch,$type=''){
        $result=null;
        $file='';
        try {
            if($batch=="0") {
                $obj = new CreditApply();
                $result = $obj->getList(1, 10000, $year, $term, $studentno, $reason, $school,$type, $audit, $verify);
                $file=$year."学年第".$term."学期学分认定汇总表（单个）";
            }
            else if($batch=="1") {
                $obj=new ProjectDetail();
                $result=$obj->getList(1,10000,$type,'',$year,$term,$reason,$studentno,$school,$verify);
                $file=$year."学年第".$term."学期学分认定汇总表（统报）";
            }
            $data=$result['rows'];
            $sheet='全部';
            $title='';
            $length=count($data);
            for($i=0;$i<$length;$i++){
                $data[$i]['audit']=$data[$i]['audit']==1?'是':'否';
                $data[$i]['verify']=$data[$i]['verify']==1?'是':'否';
            }
            $template= array("year"=>"学年","term"=>"学期","studentno"=>"学号","studentname"=>"姓名","classname"=>"班级","schoolname"=>"学院",
                "reason"=>"申请理由", "credit"=>"学分","typename"=>"类型","cerdate"=>"证书时间","applydate"=>"申请时间","audit"=>"经审核","verify"=>"经认定");
            $string=array("studentno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    //导出单个认定到word
    public function exportword($id){
        try{
            $obj = new CreditApply();
            $obj->exportWord($id);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }

}