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

use app\common\access\Item;
use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\Classes;
use app\common\service\Classplan;
use app\common\service\Course;
use app\common\service\Majorcode;
use app\common\service\Majordirection;
use app\common\service\MajorPlan;
use app\common\service\Program;
use app\common\service\R12;
use app\common\service\R30;
use app\common\service\Student;
use app\common\service\Studentplan;
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
    //复制教学计划
    public function  programcopy()
    {
        $result = null;
        $postData=$_POST['updated'];
        $postData=json_decode($postData);
        try {
            $program=new Program();
            $result = $program->copy($postData->programno,$postData->nprogramno,$postData->nprogramname,$postData->date,$postData->rem);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //培养方案复制
    public function  majorplancopy()
    {
        $result = null;
        $postData=$_POST['updated'];
        $postData=json_decode($postData);
        try {
            $obj=new MajorPlan();
            $result = $obj->copy($postData->rowid,$postData->module,$postData->rem);
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
   //导出教学计划课程
    public function programcourseexport($programno,$courseno='%',$coursename='%',$school='')
    {
        $result = null;
        try {
            $program=new R12();
            $result = $program->getList(1, 1000,$programno,$courseno,$coursename,$school);
            $progname=Item::getProgramItem($programno,false)['progname'];
            $file=$progname."课程列表";
            $data = $result['rows'];
            $sheet = '全部';
            $title = '';
            $length=count($data);
            for($i=0;$i<$length;$i++){
                $data[$i]['weeks']=implode(' ',str_split(week_dec2bin_reserve($data[$i]['weeks'],18), 4));
            }
            $template = array("courseno" => "课号", "coursename" => "课名", "credits" => "学分", "hours" => "周课时","schoolname"=>"开课学院",
                'year'=>'学年',  'term'=>'学期',  'coursetypename'=>'类别',  'examtypename'=>'考核方式',  'testname'=>'考试级别',  'categoryname'=>'课程门类',  'weeks'=>'周次');
            $string = array("programno");
            $array[] = array("sheet" => $sheet, "title" => $title, "template" => $template, "data" => $data, "string" => $string);
            PHPExcel::export2Excel($file,$array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //读取培养方案
    public function majorplanlist($page = 1, $rows = 20,$year='',$school='')
    {
        $result = null;
       // try {
            $majorplan=new MajorPlan();
            $result = $majorplan->getList($page, $rows,$year,$school);
      /*  } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }*/
        return json($result);
    }
    //更新培养方案
    public function  majorplanupdate()
    {
        $result = null;
        try {
            $obj=new MajorPlan();
            $result = $obj->update($_POST);//无法用I('post.')获取二维数组
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //导出培养方案
    public function majorplanexport($year='',$school='')
    {
        $result = null;
        try {
            $majorplan=new MajorPlan();
            $result = $majorplan->getList(1, 1000,$year,$school);
            $file="培养方案列表";
            $data = $result['rows'];
            $sheet = '全部';
            $title = '';
            $template = array("majorschool" => "专业号", "majorno" => "专业代码", "majorname" => "专业名", "direction" => "方向代码","directionname"=>"专业方向",
                'schoolname'=>'学院',  'year'=>'年级',  'module'=>'模块方向',  'credits'=>'总学分',  'mcredits'=>'必修学分',  'rem'=>'备注');
            $string = array("majorschool","majorno","direction");
            $array[] = array("sheet" => $sheet, "title" => $title, "template" => $template, "data" => $data, "string" => $string);
            PHPExcel::export2Excel($file,$array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //读取开设专业
    public function majorschool($page = 1, $rows = 20,$years='',$school='',$majorname='%')
    {
        $result = null;
        try {
            $major=new \app\common\service\Major();
            $result = $major->getList($page, $rows,$years,$school,$majorname);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //培养方案绑定的教学计划
    public function majorplanprogram($page = 1, $rows = 20,$majorplanid)
    {
        $result = null;
        try {
            $obj=new R30();
            $result = $obj->getList($page, $rows,$majorplanid);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //绑定教学计划页面检索计划
    public function programsearch($page = 1, $rows = 20,$programno='%',$progname='%',$school='')
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

    //更新培养方案绑定的教学计划信息
    public function  majorplanprogramupdate()
    {
        $result = null;
        try {
            $obj=new R30();
            $result = $obj->update($_POST);//无法用I('post.')获取二维数组
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //读取指定培养计划的绑定班级
    public function majorplanclass($page = 1, $rows = 20,$majorplanid)
    {
        $result = null;
        try {
            $obj=new Classplan();
            $result = $obj->getList($page, $rows,$majorplanid);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //更新绑定的班级培养计划
    public function  majorplanclassupdate()
    {
        $result = null;
        try {
            $obj=new Classplan();
            $result = $obj->update($_POST);//无法用I('post.')获取二维数组
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //绑定班级时检索班级
    public function searchclass($page = 1, $rows = 20,$classno='%',$classname='%',$school='')
    {
        $result = null;
        try {
            $obj=new Classes();
            $result = $obj->getList($page, $rows,$classno,$classname,$school);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //绑定培养计划到班级每个学生。
    public function  majorplanclassbind()
    {
        $result = null;
        try {
            $obj=new Classplan();
            $result = $obj->bindStudent($_POST);//无法用I('post.')获取二维数组
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //读取培养计划绑定的学生列表
    public function majorplanstudent($page = 1, $rows = 20,$majorplanid)
    {
        $result = null;
        try {
            $obj=new Studentplan();
            $result = $obj->getList($page, $rows,$majorplanid);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //读取培养计划绑定的学生列表
    public function searchstudent($page = 1, $rows = 20,$studentno='%',$name='%',$classno='%',$school='')
    {
        $result = null;
        try {
            $obj=new Student();
            $result = $obj->getList($page, $rows,$studentno,$name,$classno,$school);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //更新培养计划绑定的学生
    public function  majorplanstudentupdate()
    {
        $result = null;
        try {
            $obj=new Studentplan();
            $result = $obj->update($_POST);//无法用I('post.')获取二维数组
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //在班级培养方案里面检索班级
    public function  classplanlist($page=1,$rows=20,$classno='%',$classname='%',$school='')
    {
        $result = null;
        try {
            $obj=new Classplan();
            $result = $obj->getClassList($page,$rows,$classno,$classname,$school);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //绑定培养计划到班级每个学生。(班级方案页面)
    public function  classplanbindstudent()
    {
        $result = null;
        try {
            $obj=new Classplan();
            $result = $obj->bindStudent($_POST);//无法用I('post.')获取二维数组
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //班级方案中检索培养方案
    public function classplansearch($page = 1, $rows = 20,$year='',$school='')
    {
        $result = null;
        try {
            $majorplan=new MajorPlan();
            $result = $majorplan->getList($page, $rows,$year,$school);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //绑定到到班级|学生
    public function classplanbind()
    {
        $result = null;
        try {
            $obj=new Classplan();
            $result = $obj->bind($_POST);//无法用I('post.')获取二维数组
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //在学生方案里检索学生
    public function studentplanlist($page=1,$rows=20,$studentno='%',$name='%',$classno='%',$school='')
    {
        $result = null;
        try {
            $obj=new Studentplan();
            $result = $obj->getStudentList($page,$rows,$studentno,$name,$classno,$school);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    //学生方案页面绑定学生培养方案
    public function studentplanbind()
    {
        $result=array('info'=>'发生错误','status'=>0);
        try {
            $obj=new Studentplan();
            $effectRow = $obj->bind($_POST['studentno'],$_POST['majorplanid']);
            if($effectRow>0)
                $result=array('info'=>'绑定成功','status'=>1);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function coursesearch($page = 1, $rows = 20,$courseno='%',$coursename='%')
    {
        $result = null;
        try {
            $course=new Course();
            $result = $course->getList($page, $rows,$courseno,$coursename,'');
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
} 