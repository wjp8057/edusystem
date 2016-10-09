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

namespace app\analysis\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\Classroom;
use app\common\vendor\PHPExcel;

class Process extends MyController
{

    public function  room($page = 1, $rows = 20,$year,$term, $roomno = '%', $name = '%', $building = '%', $area = '', $equipment = '',
                              $school = '', $seatmin = 0, $seatmax = 1000)
    {
        $result=null;
        try {
            $room = new Classroom();
            $result = $room->getUsageRate($page, $rows,$year,$term, $roomno, $name, $building, $area, $equipment, $school, $seatmin, $seatmax);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
    public function exportroom($year,$term, $roomno = '%', $name = '%', $building = '%', $area = '', $equipment = '',
                          $school = '', $seatmin = 0, $seatmax = 1000,$base=40)
    {
        $result=null;
        try {
            $room = new Classroom();
            $result = $room->getUsageRate(1, 1000,$year,$term, $roomno, $name, $building, $area, $equipment, $school, $seatmin, $seatmax);
            $data = $result['rows'];
            $file = $year."学年第".$term."学期教室利用率（".$base."课时基准)";
            $sheet = '全部';
            $title = '';
            foreach ($data as $one) {
                $one['rate']=$one['used'];
            }
            $template = array("roomno" => "教室号", "no" => "房间号","roomname"=>"名称", "building" => "楼名", "areaname" => "校区","seats"=>"座位数",
                "equipmentname"=>"设施", "used"=>"周课时","rate"=>"利用率");
            $string = array("roomno,no");
            $array[] = array("sheet" => $sheet, "title" => $title, "template" => $template, "data" => $data, "string" => $string);
            PHPExcel::export2Excel($file, $array);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
} 