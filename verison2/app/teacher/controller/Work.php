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
            $result = $work->getTeacherWorkList($page, $rows, $teacherno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
}