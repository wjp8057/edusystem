<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 9:54
 */
namespace app\score\controller;
use app\common\access\Item;
use app\common\access\Template;
use \app\common\service\Action;
use \app\common\access\MyAccess;
use app\common\service\Makeup;
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
            $title=$year."学年第".$term."学期期末成绩登记表";
            $this->assign('title',$title);
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
            return $this->fetch("all@index/score");
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }

    }
    function printfinalviewblank($year='',$term='',$courseno='',$page=1){
        try{
            //头部信息
            $title=$year."学年第".$term."学期期末成绩登记表(空白)";
            $this->assign('title',$title);
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
                $scorestring.=$i<$amount?'<td>'.$result[$i]["studentno"].'</td><td>'.$result[$i]["name"].'</td><td>&nbsp;</td>':'<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $scorestring.=($i+40)<$amount?'<td>'.$result[$i+40]["studentno"].'</td><td>'.$result[$i+40]["name"].'</td><td>&nbsp;</td>':'<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $scorestring.=($i+80)<$amount?'<td>'.$result[$i+80]["studentno"].'</td><td>'.$result[$i+80]["name"].'</td><td>&nbsp;</td>':'<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $scorestring.='</tr>';
            }
            $this->assign('score',$scorestring);
            $summary=null;;
            $this->assign('summary',$summary);
            return $this->fetch("all@index/score");
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }

    }
    /**学期初补考成绩输入界面
     * @param string $year
     * @param string $term
     * @param string $courseno
     * @param string $type
     * @return mixed
     */
    function recordbegininput($year = '', $term = '', $courseno = '', $type = '')
    {
        try {
            $scoreType = '未知';
            switch ($type) {
                case 't':
                    $scoreType = '两级制';
                    break;
                case 'f':
                    $scoreType = '五级制';
                    break;
                case 'h':
                    $scoreType = '百分制';
                    break;
                default:
                    break;
            }
            $title = $year . '学年第' . $term . '学期学期初补考成绩输入(' . $scoreType . ')';
            $this->assign('title', $title);
            $coursename=Item::getCourseItem($courseno)['coursename'];
            $course = '课号：' . $courseno . ' 课名：' . $coursename;
            $this->assign('course', $course);

            $makeup = new Makeup();
            $examdate = $makeup->getCourseExamDate($year, $term, $courseno);
            $this->assign('examdate', $examdate);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }
    /**
     * 成绩输入-打印成绩单
     * @param string $year
     * @param string $term
     * @param string $courseno
     * @param int $page
     * @return mixed
     */
    function recordbeginprint($year = '', $term = '', $courseno = '', $page = 1)
    {
        try {
            //头部信息
            $this->assign('year', $year);
            $this->assign('term', $term);
            $makeup=new Makeup();
            $course = $makeup->getCourseInfo($year, $term, $courseno);
            $this->assign('course', $course);
            //成绩信息
            $score = new Makeup();
            $student = $score->getStudentList($page, 120, $year, $term, $courseno);
            $result = $student['rows'];
            $amount = count($result);//当前页行数
            $scorestring = '';
            for ($i = 0; $i < 40; $i++) {
                $scorestring .= '<tr>';
                $scorestring .= $i < $amount ? '<td>' . $result[$i]["studentno"] . '</td><td>' . $result[$i]["name"] . '</td><td>' . $result[$i]["printscore"] . '</td>' : '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $scorestring .= ($i + 40) < $amount ? '<td>' . $result[$i + 40]["studentno"] . '</td><td>' . $result[$i + 40]["name"] . '</td><td>' . $result[$i + 40]["printscore"] . '</td>' : '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $scorestring .= ($i + 80) < $amount ? '<td>' . $result[$i + 80]["studentno"] . '</td><td>' . $result[$i + 80]["name"] . '</td><td>' . $result[$i + 80]["printscore"] . '</td>' : '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                $scorestring .= '</tr>';
            }
            $this->assign('score', $scorestring);

            $summary = $score->getCoursePercent($year, $term, $courseno);
            $this->assign('summary', $summary);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();

    }

    function creditdate(){
        try {
            $valid=Item::getValidItem('A');
            $this->assign('valid', $valid);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }
    //创新技能项目学生名单
    function creativestudent($id){
        try {
            $project=Item::getProjectItem($id);
            $this->assign('project', $project);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }
    //素质学分项目学生名单
    function qualitystudent($id){
        try {
            $project=Item::getProjectItem($id);
            $this->assign('project', $project);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return $this->fetch();
    }
}