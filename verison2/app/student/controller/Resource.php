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
// | Created:2016/12/9 8:09
// +----------------------------------------------------------------------

namespace app\student\controller;

//公共资源
use app\common\access\MyAccess;
use app\common\service\Classroom;

class Resource {
    public function  room($page = 1, $rows = 20, $year = '', $term = '', $roomno = '%', $name = '%', $building = '%', $area = '', $equipment = '',
                                   $school = '', $seatmin = 0, $seatmax = 1000, $weekday = '', $oew = '', $time = '')
    {
        $result=null;
        try {
            $room = new Classroom();
            $result = $room->getUsageList($page, $rows, $year, $term, $roomno, $name, $building, $area, $equipment, $school, $seatmin, $seatmax, $weekday, $oew, $time);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
}