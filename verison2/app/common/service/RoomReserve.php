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

namespace app\common\service;


use app\common\access\Item;
use app\common\access\MyException;
use app\common\access\MyService;
use think\Db;
use think\Exception;

/**教室借用
 * Class RoomReserve
 * @package app\common\service
 */
class RoomReserve extends MyService{

    /**获取教室借用列表
     * @param int $page
     * @param int $rows
     * @param $year
     * @param $term
     * @param string $roomno
     * @param string $school
     * @param string $approved
     * @return array|null
     */
    public function getList($page=1,$rows=20,$year,$term,$roomno='%',$school='',$approved=''){
        $result=null;
        $condition=null;
        $condition['roomreserve.year']=$year;
        $condition['roomreserve.term']=$term;
        if($roomno!='%') $condition['roomreserve.roomno']=array('like',$roomno);
        if($school!='')  $condition['roomreserve.school']=$school;
        if($approved!='') $condition['roomreserve.approved']=$approved;
        $data=$this->query->table('roomreserve')->join('classrooms ','classrooms.roomno=roomreserve.roomno')
            ->join('users','users.username=roomreserve.username')
            ->join('teachers','teachers.teacherno=users.teacherno')
            ->join ('roomoptions','roomoptions.name=classrooms.equipment')
            ->join('timesections','timesections.name=roomreserve.time')
            ->join('schools','schools.school=roomreserve.school')
            ->page($page,$rows)
            ->field('classrooms.roomno,rtrim(classrooms.jsn) roomname,rtrim(teachers.name) teachername,
            rtrim(schools.name) schoolname,classrooms.status,classrooms.reserved,rtrim(timesections.value) as timename,
            roomreserve.day,convert(varchar,applydate,120) applydate,weeks,rtrim(roomoptions.value) equipment,rtrim(purpose) purpose,approved,recno')
            ->where($condition)->order('applydate desc')->select();
        $count= $this->query->table('roomreserve')->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

    /**根据记录好获取借用教室的信息
     * @param $recno
     * @return mixed
     * @throws \think\Exception
     */
    public function getReserveRoomInfo($recno){
        $condition['recno']=$recno;
        $result=$this->query->table('roomreserve')->join('classrooms','classrooms.roomno=roomreserve.roomno')
            ->join('schools','schools.school=roomreserve.school')
            ->join('timesections','timesections.name=roomreserve.time')
            ->where($condition)->field('roomreserve.year,roomreserve.term,rtrim(classrooms.building) building,classrooms.roomno,rtrim(classrooms.jsn) roomname,
            rtrim(schools.name) schoolname,rtrim(roomreserve.purpose) purpose,roomreserve.weeks,rtrim(timesections.value) timename,roomreserve.day,roomreserve.approved')
            ->select();
        if(!is_array($result)||count($result)!=1)
            throw new Exception('recno'.$recno, MyException::PARAM_NOT_CORRECT);
        return $result[0];
    }

    /**借用教室
     * @param $year
     * @param $term
     * @param $roomno
     * @param $day
     * @param $time
     * @param $weeks
     * @param $purpose
     * @return array
     * @throws Exception
     */
    public function apply($year,$term,$roomno,$day,$time,$weeks,$purpose){

        if($this->checkRoomFree($roomno)) {
            //检查是否与教学安排冲突
            $timebit = Item::getTimeSectionItem($time)['timebits'];

            $conflict = $this->checkCourseConflict($year, $term, $roomno, $day, $timebit, $weeks);
            if ($conflict) {
                //检查是否与借用信息冲突
                $conflict = $this->checkReserveConflict($year, $term, $roomno, $day, $timebit, $weeks);
                if ($conflict) {
                    //没问题添加借用记录
                    $data['year'] = $year;
                    $data['term'] = $term;
                    $data['roomno'] = $roomno;
                    $data['day'] = $day;
                    $data['time'] = $time;
                    $data['weeks'] = $weeks;
                    $data['purpose'] = $purpose;
                    $data['school'] = session('S_USER_SCHOOL');
                    $data['username'] = session('S_USER_NAME');
                    $data['oew'] = 'B';//默认就放B 单双周，实际上有周次了和单双无关
                    $this->query->table('roomreserve')->insert($data);
                    return ["status" => "1", "info" => "申请成功，请等待批准。"];
                }
                return ["status" => "0", "info" => "该时间段已被借用"];
            }
            return ["status" => "0", "info" => "与课程安排冲突"];
        }
        return ["status" => "0", "info" => "教室被保留或不可用"];
    }

    /**
     * @param $roomno
     * @return bool
     * @throws Exception
     */
    public function checkRoomFree($roomno){
        $result=Item::getRoomItem($roomno);
        if($result['reserved']=="1"||$result['status']=="0")
            return false;
        else
            return true;
    }

    /**检查与课程是否冲突
     * @param $year
     * @param $term
     * @param $roomno
     * @param $day
     * @param $timebit
     * @param $weeks
     * @return bool
     * @throws \think\Exception
     */
    private function checkCourseConflict($year,$term,$roomno,$day,$timebit,$weeks){
        $condition['year']=$year;
        $condition['term']=$term;
        $condition['day']=$day;
        $condition['roomno']=$roomno;
        if(!is_numeric($weeks)||!is_numeric($timebit))
            throw new Exception('week'.$weeks.',time'.$timebit, MyException::PARAM_NOT_CORRECT);
        $result=$this->query->table('schedule')->join('timesections','timesections.name=schedule.time')
            ->join('oewoptions','oewoptions.code=schedule.oew')
            ->field('dbo.GROUP_OR(oewoptions.timebit2&schedule.weeks)&'.$weeks.' as weekbit')
            ->where($condition)->where('timesections.TIMEBITS&'.$timebit.'!=0')->find();
        if($result['weekbit']!=0)
            return false;
        return true;
    }

    /**检查与借用是否冲突
     * @param $year
     * @param $term
     * @param $roomno
     * @param $day
     * @param $timebit
     * @param $weeks
     * @return bool
     * @throws \think\Exception
     */
    private function checkReserveConflict($year,$term,$roomno,$day,$timebit,$weeks){
        $condition['year']=$year;
        $condition['term']=$term;
        $condition['day']=$day;
        $condition['roomno']=$roomno;
        if(!is_numeric($weeks)||!is_numeric($timebit))
            throw new Exception('week'.$weeks.',time'.$timebit, MyException::PARAM_NOT_CORRECT);
        $result=$this->query->table('roomreserve')->join('timesections','timesections.name=roomreserve.time')
            ->field('dbo.GROUP_OR(roomreserve.weeks)&'.$weeks.' as weekbit')
            ->where($condition)->where('timesections.TIMEBITS&'.$timebit.'!=0')->find();
        if($result['weekbit']!=0)
            return false;
        return true;
    }

    /**更新教室借用记录（删除，审批）
     * @param $postData
     * @return array
     * @throws \Exception
     */
    function update($postData){
        $updateRow=0;
        $deleteRow=0;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['recno'] = $one->recno;
                    $data['approved'] = $one->approved;
                    $updateRow += $this->query->table('roomreserve')->where($condition)->update($data);
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['recno'] = $one->recno;
                    $deleteRow += $this->query->table('roomreserve')->where($condition)->delete();
                }
            }
        }
        catch(\Exception $e){
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        $info='';
        if($updateRow>0) $info.=$updateRow.'条更新！</br>';
        if($deleteRow>0) $info.=$deleteRow.'条删除！</br>';
        $status=1;
        if($info=='') {
            $info="没有数据被更新";
            $status=0;
        }
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }
} 