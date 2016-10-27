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

namespace app\major\controller;

use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\Majorcode;
use app\common\service\Majordirection;
use app\common\service\Program;
use app\common\service\R12;
use app\common\vendor\PHPExcel;

class Plan extends MyController
{

    //读取教学计划
    public function programlist($page = 1, $rows = 20,$programno='%',$progname='%',$school='')
    {
        $result = null;
        try {
            $program=new Program();
            $result = $program->getList($page, $rows,$programno,$progname,$school);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //更新开设专业信息
    //更新专业方向
    public function  programupdate()
    {
        $result = null;
        try {
            $program=new Program();
            $result = $program->update($_POST);//无法用I('post.')获取二维数组
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //导出开设专业
    public function programexport($programno='%',$programname='%',$school='')
    {
        $result = null;
        try {
            $program=new Program();
            $result = $program->getList(1,1000,$programno,$programname,$school);
            $file="教学计划";
            $data = $result['rows'];
            $sheet = '全部';
            $title = '';
            $template = array("programno" => "教学计划号", "progname" => "计划名称", "date" => "制定之间", "validname" => "有效","schoolname"=>"学院",
            "url"=>"网址","rem"=>"备注");
            $string = array("programno");
            $array[] = array("sheet" => $sheet, "title" => $title, "template" => $template, "data" => $data, "string" => $string);
            PHPExcel::export2Excel($file,$array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //读取教学计划
    public function programcourselist($page = 1, $rows = 20,$programno,$courseno='%',$coursename='%',$school='')
    {
        $result = null;
        try {
            $program=new R12();
            $result = $program->getList($page, $rows,$programno,$courseno,$coursename,$school);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
   //更新教学计划中的课程信息
    public function  programcourseupdate()
    {
        $result = null;
        try {
            $obj=new R12();
            $result = $obj->update($_POST);//无法用I('post.')获取二维数组
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
} 