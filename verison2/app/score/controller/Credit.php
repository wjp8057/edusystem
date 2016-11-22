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


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\CreditApply;
use app\common\service\Project;
use app\common\service\ProjectDetail;
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
    public function exportcreative($year,$term,$studentno='%',$reason='%',$school='',$audit=''){

        $result=null;
        try {
            $obj = new CreditApply();
            $result = $obj->getList(1,10000,$year,$term,$studentno,$reason,$school,'A',$audit)['rows'];
            $data=$result['rows'];
            $file=$year."学年第".$term."学期创新技能认定汇总表（单个）";
            $sheet='全部';
            $title='';
            $length=count($data);
            for($i=0;$i<$length;$i++){
                $data[$i]['audit']=$data[$i]['audit']==1?'是':'否';
                $data[$i]['verify']=$data[$i]['verify']==1?'是':'否';
            }
            $template= array("year"=>"学年","term"=>"学期","studentno"=>"学号","studentname"=>"姓名","classname"=>"班级","schoolname"=>"班级",
                "reason"=>"申请理由", "credit"=>"学分","cerdate"=>"证书时间","applydate"=>"申请时间","audit"=>"经审核","verify"=>"经认定");
            $string=array("studentno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }


    }
}