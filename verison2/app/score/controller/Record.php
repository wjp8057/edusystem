<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:18
 */
namespace app\score\controller;

use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\Makeup;
use app\common\service\ViewFinalScoreCourse;

class Record extends MyController
{
    /**读取补考课程列表
     * @param int $page
     * @param int $rows
     * @param string $year
     * @param string $term
     * @param string $courseno
     * @param string $school
     * @return \think\response\Json
     */
    public function begincourse($page = 1, $rows = 20, $year = '', $term = '', $courseno = '%',$school = '',$type='')
    {
        $result = null;
        try {
            $course = new Makeup();
            $result = $course->getCourseList($page, $rows, $year, $term, $courseno, $school,$type);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    /**获取学期初补考学生名单
     * @param int $page
     * @param int $rows
     * @param string $year
     * @param string $term
     * @param $courseno
     * @return \think\response\Json
     */
    public function beginstudent($page = 1, $rows = 20, $year = '', $term = '', $courseno)
    {
        $result = null;
        try {
            $makeup= new Makeup();
            $result = $makeup->getStudentList($page, $rows, $year, $term, $courseno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    /** 更新学期初补考学生成绩
     * @return \think\response\Json
     */
    public function beginupdate()
    {
        $result = null;
        try {
            $score = new Makeup();
            $result = $score->updateScore($_POST);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    public function beginunlock($year,$term,$courseno)
    {
        $result = null;
        try {
            $score = new Makeup();
            $result = $score->unlockCourse($year,$term,$courseno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

}