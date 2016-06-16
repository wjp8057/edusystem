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

namespace app\base\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\Area;
use app\common\service\Classroom;
use app\common\service\RoomOption;
use app\common\service\RoomReserve;
use app\common\vendor\PHPExcel;

class Room extends MyController
{
    /**读取校区信息
     * @param int $page
     * @param int $rows
     */
    public function area($page = 1, $rows = 20)
    {
        try {
            $area = new Area();
            $result = $area->getList($page, $rows);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 更新校区信息
     */
    public function  updatearea()
    {
        try {
            $area = new Area();
            $result = $area->update($_POST);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    /**教室类型
     * @param int $page
     * @param int $rows
     */
    public function option($page = 1, $rows = 20)
    {
        try {
            $option= new RoomOption();
            $result = $option->getList($page, $rows);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 更新教室类型
     */
    public function updateoption()
    {
        try {
            $option= new RoomOption();
            $result = $option->update($_POST);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    public function updateroom(){
        try {
            $option= new Classroom();
            $result = $option->update($_POST);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    /**获取教室列表
     * @param int $page
     * @param int $rows
     * @param string $roomno
     * @param string $name
     * @param string $building
     * @param string $area
     * @param string $equipment
     * @param string $school
     * @param string $status
     * @param string $reserved
     * @param int $seatmin
     * @param int $seatmax
     * @param int $testmin
     * @param int $testmax
     * @return \think\response\Json
     */
    public function  roomlist($page = 1, $rows = 20, $roomno = '%', $name = '%', $building = '%', $area = '', $equipment = '',
                              $school = '', $status = '', $reserved = '', $seatmin = 0, $seatmax = 1000, $testmin = 0, $testmax = 1000)
    {
        try {
            $room = new Classroom();
            $result = $room->getList($page, $rows, $roomno, $name, $building, $area, $equipment, $school, $status, $reserved, $seatmin, $seatmax, $testmin, $testmax);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    public function  exportroom( $roomno = '%', $name = '%', $building = '%', $area = '', $equipment = '',
                                $school = '', $status = '', $reserved = '', $seatmin = 0, $seatmax = 1000, $testmin = 0, $testmax = 1000){
        try {
            $room = new Classroom();
            $result = $room->getList(1, 10000, $roomno, $name, $building, $area, $equipment, $school, $status, $reserved, $seatmin, $seatmax, $testmin, $testmax);
            $data=$result['rows'];
            $file="教室汇总表";
            $sheet='全部';
            $title='教室汇总表';
            $length=count($data);
            for($i=0;$i<$length;$i++){
                $data[$i]['status']=$data[$i]['status']==1?'是':'否';
                $data[$i]['reserved']=$data[$i]['reserved']==1?'是':'否';
            }
            $template= array("roomno"=>"教室号","no"=>"房间号","roomname"=>"教室名","building"=>"楼名","areaname"=>"校区","equipment"=>"设施",
                "seats"=>"座位数", "testers"=>"考位数","schoolname"=>"优先学院","usagename"=>"排课约束","reserved"=>"保留","status"=>"可用");
            $string=array("roomno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    /**获取教室使用情况表
     * @param int $page
     * @param int $rows
     * @param string $year
     * @param string $term
     * @param string $roomno
     * @param string $name
     * @param string $building
     * @param string $area
     * @param string $equipment
     * @param string $school
     * @param int $seatmin
     * @param int $seatmax
     * @param string $weekday
     * @param string $oew
     * @param string $time
     */
    public function  roomusagelist($page = 1, $rows = 20, $year = '', $term = '', $roomno = '%', $name = '%', $building = '%', $area = '', $equipment = '',
                                   $school = '', $seatmin = 0, $seatmax = 1000, $weekday = '', $oew = '', $time = '')
    {
        try {
            $room = new Classroom();
            $result = $room->getUsageList($page, $rows, $year, $term, $roomno, $name, $building, $area, $equipment, $school, $seatmin, $seatmax, $weekday, $oew, $time);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    /**刷新教室资源时间表
     * @param $year
     * @param $term
     */
    public function refresh($year,$term){
        try {
            $room = new Classroom();
            if($room->refreshTimeList($year,$term))
            {
                $result=['info'=>'教室资源时间表刷新成功。','status'=>'1'];
            }
            else{
                $result=['info'=>'刷新失败,未知错误！','status'=>'0'];
            }
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    /**教室借用列表
     * @param int $page
     * @param int $rows
     * @param $year
     * @param $term
     * @param string $roomno
     * @param string $school
     * @param string $approved
     */
    public function reservelist($page=1,$rows=20,$year,$term,$roomno='%',$school='',$approved=''){
        try {
            $option= new RoomReserve();
            $result = $option->getList($page, $rows,$year,$term,$roomno,$school,$approved);
            return json($result);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    /**导出借用教室列表
     * @param $year string 学年
     * @param $term string 学期
     * @param string $roomno 教室号
     * @param string $school 借用学院
     * @param string $approved 审批
     */
    public function exportreserve($year,$term,$roomno='%',$school='',$approved=''){
        try {
            $reserve= new RoomReserve();
            $result = $reserve->getList(1,10000,$year,$term,$roomno,$school,$approved);
            $data=$result['rows'];
            $length=count($data);
            for($i=0;$i<$length;$i++){
                $data[$i]['weeks']=implode(' ',str_split(week_dec2bin_reserve($data[$i]['weeks'],20), 4));
                $data[$i]['approved']=$data[$i]['approved']==1?'已批准':'';
                $data[$i]['status']=$data[$i]['status']==1?'是':'否';
                $data[$i]['reserved']=$data[$i]['reserved']==1?'是':'否';
            }
            $file="教室借用汇总表";
            $sheet='全部';
            $title='教室借用汇总表';
            $template= array("approved"=>"批准","status"=>"可用","reserved"=>"保留","roomno"=>"教室号","roomname"=>"教室名","equipment"=>"设施",
                "day"=>"星期", "timename"=>"节次","weeks"=>"周次","schoolname"=>"借用单位","teachername"=>"申请人","applydate"=>"申请时间","purpose"=>"用途");
            $string=array("roomno");
            $array[]=array("sheet"=>$sheet,"title"=>$title,"template"=>$template,"data"=>$data,"string"=>$string);
            PHPExcel::export2Excel($file,$array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    /**教室借用申请
     *
     */
    public function  roomapply($year,$term,$roomno,$day,$time,$weeks,$purpose){
        try {
            $reserve= new RoomReserve();
            $result=$reserve->apply($year,$term,$roomno,$day,$time,$weeks,$purpose);
            return json($result);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    public function updatereserve(){
        try {
            $reserve= new RoomReserve();
            $result=$reserve->update($_POST);
            return json($result);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }

    public function getroomname($roomno){
        try {
            $room= new Classroom();
            $result=$room->getRoomItemByNo($roomno)['name'];
            return json(["status"=>1,'info'=>$result]);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
} 