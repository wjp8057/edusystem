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
use app\common\service\QualityFile;

/**教学质量评估
 * Class Quality
 * @package app\teacher\controller
 */
class Quality extends MyController
{
    /**读取教师个人教学质量评估得分
     * @param int $page
     * @param int $rows
     * @return \think\response\Json
     */
    public function query($page = 1, $rows = 20)
    {
        $result = null;
        try {
            $quality = new QualityFile();
            $teacherno = session('S_TEACHERNO');
            $result = $quality->getTeacherQualityList($page, $rows, $teacherno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
}