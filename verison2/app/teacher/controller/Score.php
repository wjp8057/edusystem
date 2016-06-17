<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:42
 */

namespace app\teacher\controller;


use app\common\access\MyController;
use app\common\access\MyAccess;
use app\common\service\ViewTeacherCourse;

/**成绩
 * Class Score
 * @package app\teacher\controller
 */
class Score extends MyController
{
    /**显示当前登录账户本人的成绩输入
     * @param int $page
     * @param int $rows
     * @param string $year
     * @param string $term
     * @return \think\response\Json
     */
    public function course($page = 1, $rows = 20, $year = '2014', $term = '1')
    {
        $result = null;
        try {
            $course = new ViewTeacherCourse();
            $teacherno = session('S_TEACHERNO');
            $result = $course->getCourseList($page, $rows, $year, $term, $teacherno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    /**读取课程成绩单中的相关学生列表
     * @param int $page
     * @param int $rows
     * @param string $year
     * @param string $term
     * @param string $courseno
     * @return \think\response\Json
     */
    public function query($page = 1, $rows = 20, $year = '', $term = '', $courseno = '')
    {
        $result = null;
        try {
            $score = new \app\common\service\Score();
            $result = $score->getStudentList($page, $rows, $year, $term, $courseno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    /** 更新课程学生成绩
     * @return \think\response\Json
     */
    public function update()
    {
        $result = null;
        try {
            $score = new \app\common\service\Score();
            $result = $score->updateScore($_POST);//无法用I('post.')获取二维数组

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
}