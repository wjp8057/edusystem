<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:18
 */
namespace app\plan\controller;

use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\Schedulechange;

class Change extends MyController
{

    public function summary($page = 1, $rows = 20, $year = '', $term = '',$status='')
    {
        $result = null;
        try {
            $summary = new Schedulechange();
            $result = $summary->getSummary($page, $rows, $year, $term, $status);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

}