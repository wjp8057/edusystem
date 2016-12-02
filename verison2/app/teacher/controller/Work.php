<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:42
 */

namespace app\teacher\controller;

use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\WorkFile;
use app\common\vendor\PHPExcel;

/**工作量
 * Class Work
 * @package app\teacher\controller
 */
class Work extends MyController
{
    public function query($page = 1, $rows = 20)
    {
        $result = null;
        try {
            $work = new WorkFile();
            $teacherno = session('S_TEACHERNO');
            $result = $work->getList($page, $rows, $teacherno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    public function export(){

        $result=null;
        try {
            $obj=new WorkFile();
            $teacherno = session('S_TEACHERNO');
            $result = $obj->getList(1, 1000, $teacherno);
            $file="本人历年工作量详细表";
            $data=$result['rows'];
            $sheet='全部';
            $title=$file;
            $template= array("year"=>"学年","term"=>"学期","courseno"=>"课号","coursename"=>"课名","work"=>"工作量","attendent"=>"学生数","classname"=>"班级");
            $string=array("studentno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
}