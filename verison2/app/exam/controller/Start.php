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
// | Created:2016/12/9 11:21
// +----------------------------------------------------------------------

namespace app\exam\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\Makeup;
use app\common\vendor\PHPExcel;

class Start extends  MyController{
    //读取补考学生名单
    public function  query($page = 1, $rows = 20,$year,$term,$courseno='%',$studentno='%',$courseschool='',$studentschool='',$examrem='')
    {
        $result=null;
        try {
            $obj=new Makeup();
            $result=$obj->getList($page,$rows,$year,$term,$courseno,$studentno,$courseschool,$studentschool,$examrem);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //圣痕补考名单
    public function  init($year,$term)
    {
        $result=null;
        try {
            $obj=new Makeup();
            $result=$obj->init($year,$term);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    public function export($year,$term,$courseno='%',$studentno='%',$courseschool='',$studentschool='',$examrem=''){
        try{
            $obj=new Makeup();
            $result=$obj->getList(1,5000,$year,$term,$courseno,$studentno,$courseschool,$studentschool,$examrem);
            $data=$result['rows'];
            $file=$year."学年第".$term."学期学期初补考名单";
            $sheet='全部';
            $title=$file;
            $template= array("courseno"=>"课号","coursename"=>"课名","courseschoolname"=>"开课学院","studentno"=>"学号","studentname"=>"姓名",
                "classname"=>"班级","plantypename"=>"类型","studentschoolname"=>"所在学院","qm"=>"期末","score"=>"总评","examremname"=>"考试备注");
            $string=array("studentno","courseno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
    //更新名单
    public function update()
    {
        $result=null;
        try {
            $obj=new Makeup();
            $result=$obj->update($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

}