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

namespace app\quality\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\QualityExpert;
use app\common\vendor\PHPExcel;
//督导评教
class Expert extends MyController
{
    /**读取专家列表（全部名单中筛选）
     * @param int $page
     * @param int $rows
     * @param string $teacherno
     * @param string $name
     * @param string $school
     * @return \think\response\Json
     */
    public function expertlist($page = 1, $rows = 20, $teacherno = '%', $name = '%', $school = '')
    {
        $result = null;
        try {
            $teacher = new \app\common\service\Teacher();
            $result = $teacher->getList($page, $rows, $teacherno, $name, $school);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    /**检索教师列表
     * @param int $page
     * @param int $rows
     * @param string $teacherno
     * @param string $name
     * @param string $school
     * @return \think\response\Json
     */
    public function teacherlist($page = 1, $rows = 20, $teacherno = '%', $name = '%', $school = '')
    {
        $result = null;
        try {
            $teacher = new \app\common\service\Teacher();
            $result = $teacher->getList($page, $rows, $teacherno, $name, $school);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }



    /**专家打分情况表
     * @param int $page
     * @param int $rows
     * @param $year
     * @param $term
     * @param $teacherno
     * @return \think\response\Json
     */
    public function scorelist($page = 1, $rows = 20,$year,$term, $expert)
    {
        $result = null;
        try {
            $obj = new QualityExpert();
            $result = $obj->getList($page, $rows, $year,$term,$expert);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    /**更新专家打分
     * @return \think\response\Json
     */
    public function scoreupdate()
    {
        $result = null;
        try {
            $obj = new QualityExpert();
            $result = $obj->update($_POST);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    /**计算某个专家的打分成绩归一分
     * @param $year
     * @param $term
     * @param $expert
     * @param $standard
     * @return \think\response\Json
     */
    public function calculate($year,$term,$expert,$standard)
    {
        $result = null;
        try {
            $obj = new QualityExpert();
            $result = $obj->calculate($year,$term,$expert,$standard);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    /**计算全部专家的打分成绩归一分
     * @param $year
     * @param $term
     * @param $standard
     * @return \think\response\Json
     */
    public function calculateall($year,$term,$standard)
    {
        $result = null;
        try {
            $obj = new QualityExpert();
            $result = $obj->calculate($year,$term,'%',$standard);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    /**专家打分查询，汇总打分次数
     * @param int $page
     * @param int $rows
     * @param $year
     * @param $term
     * @param string $expert
     * @param string $name
     * @param string $school
     * @return \think\response\Json
     */
    public function expertscorelist($page = 1, $rows = 20,$year,$term,$expert = '%', $name = '%', $school = '')
    {
        $result = null;
        try {
            $obj = new QualityExpert();
            $result = $obj->getScoreSummary($page, $rows, $year,$term,$expert, $name, $school);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    /**教师成绩查询，计算好平均分
     * @param int $page
     * @param int $rows
     * @param $year
     * @param $term
     * @param string $teacherno
     * @param string $name
     * @param string $school
     * @return \think\response\Json
     */
    public function teacherscorelist($page = 1, $rows = 20,$year,$teacherno = '%', $name = '%', $school = '')
    {
        $result = null;
        try {
            $obj = new QualityExpert();
            $result = $obj->getTeacherScore($page, $rows, $year,$teacherno, $name, $school);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function teacherexpert($page = 1, $rows = 20,$year,$teacherno)
    {
        $result = null;
        try {
            $obj = new QualityExpert();
            $result = $obj->getTeacherExpert($page, $rows, $year,$teacherno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    public function exportteacherscore($year,$teacherno='%',$name='%',$school='')
    {
        try{
            $obj = new QualityExpert();
            $result = $obj->getTeacherScore(1, 10000, $year,$teacherno, $name, $school);
            $data = $result['rows'];
            $file = $year."学年教师得分表";
            $sheet = '全部';
            $title = '';
            $template = array("teacherno" => "教师号", "teachername" => "姓名","typename"=>"类型", "schoolname" => "学院", "score" => "平均分");
            $string = array("teacherno");
            $array[] = array("sheet" => $sheet, "title" => $title, "template" => $template, "data" => $data, "string" => $string);
            PHPExcel::export2Excel($file, $array);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }

    }

    public function exportscore($year,$term,$expert='%',$name='%',$school='')
    {
        try{
            $obj = new QualityExpert();
            $result = $obj->getList(1,10000,$year,$term,$expert,'%',$name,$school);
            $data = $result['rows'];
            $file = $year."学年第".$term."学期教师得分表";
            $sheet = '全部';
            $title = '';
            $template = array('expertname'=>'督导',"teacherno" => "教师号", "teachername" => "姓名","typename"=>"类型", "schoolname" => "学院", "score" => "得分",'normalscore'=>'归一分','rem'=>'备注');
            $string = array("teacherno");
            $array[] = array("sheet" => $sheet, "title" => $title, "template" => $template, "data" => $data, "string" => $string);
            PHPExcel::export2Excel($file, $array);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }

    }
}