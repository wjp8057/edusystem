<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 9:54
 */
namespace app\score\controller;
use app\common\access\Template;
use \app\common\service\Action;
use \app\common\access\MyAccess;
use app\common\service\ViewScheduleTable;

class Index extends  Template {
    /*
    * 基础数据首页
    */
    public function index(){
        $obj=new  Action();
        $menuJson=array('menus'=>$obj->getUserAccessMenu(session('S_USER_NAME'),312));
        $this->assign('menu',json_encode($menuJson));
        return $this->fetch();
    }
    function printfinalview($year='',$term='',$courseno='',$page=1){
        try{
            //头部信息
            $this->assign('year',$year);
            $this->assign('term',$term);
            $schedule=new ViewScheduleTable();
            $course=$schedule->getCourseInfo($year,$term,$courseno);
            $this->assign('course',$course);
            //成绩信息
            $score=new \app\common\service\Score();
            $student=$score->getStudentList($page,120,$year,$term,$courseno);
            $result=$student['rows'];
            $amount=count($result);//当前页行数
            $scorestring='';
            for($i=0;$i<40;$i++){
                $scorestring.='<tr>';
                $scorestring.=$i<$amount?'<td>'.$result[$i]["studentno"].'</td><td>'.$result[$i]["name"].'</td><td>'.$result[$i]["printscore"].'</td>':'<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $scorestring.=($i+40)<$amount?'<td>'.$result[$i+40]["studentno"].'</td><td>'.$result[$i+40]["name"].'</td><td>'.$result[$i+40]["printscore"].'</td>':'<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $scorestring.=($i+80)<$amount?'<td>'.$result[$i+80]["studentno"].'</td><td>'.$result[$i+80]["name"].'</td><td>'.$result[$i+80]["printscore"].'</td>':'<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $scorestring.='</tr>';
            }
            $this->assign('score',$scorestring);
            $summary=$score->getCoursePercent($year,$term,$courseno);
            $this->assign('summary',$summary);
            return $this->fetch();
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }

    }
}