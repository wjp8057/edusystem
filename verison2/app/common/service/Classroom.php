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


use app\common\access\MyException;
use app\common\access\MyService;
use think\Db;
use think\Exception;
use think\Log;

class Classroom extends MyService {
    function update($postData){
        $updateRow=0;
        $deleteRow=0;
        $insertRow=0;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["inserted"])) {
                $updated = $postData["inserted"];
                $listUpdated = json_decode($updated);
                $data = null;
                foreach ($listUpdated as $one) {
                    $data['roomno'] = $one->roomno;
                    $data['area'] = $one->area;
                    $data['building'] = $one->building;
                    $data['no'] = $one->no;
                    $data['jsn'] = $one->roomname;
                    $data['seats'] = $one->seats;
                    $data['testers'] = $one->testers;
                    $data['equipment'] = $one->equipment;
                    $data['status'] = $one->status;
                    $data['priority'] = $one->school;
                    $data['usage'] = $one->usage;
                    $data['reserved'] = $one->reserved;

                    $row = $this->query->table('classrooms')->insert($data);
                    if ($row > 0)
                        $insertRow++;
                }
            }
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['roomno'] = $one->roomno;
                    $data['area'] = $one->area;
                    $data['building'] = $one->building;
                    $data['no'] = $one->no;
                    $data['jsn'] = $one->roomname;
                    $data['seats'] = $one->seats;
                    $data['testers'] = $one->testers;
                    $data['equipment'] = $one->equipment;
                    $data['status'] = $one->status;
                    $data['priority'] = $one->school;
                    $data['usage'] = $one->usage;
                    $data['reserved'] = $one->reserved;
                    $updateRow += $this->query->table('classrooms')->where($condition)->update($data);
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['roomno'] = $one->roomno;
                    $deleteRow += $this->query->table('classrooms')->where($condition)->delete();
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
        if($insertRow>0) $info.=$insertRow.'条添加！</br>';
        $status=1;
        if($info=='') {
            $info="没有数据被更新";
            $status=0;
        }
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }
    /**获取教室列表
     * @param int $page
     * @param int $rows
     * @param string $roomno   教室号
     * @param string $name   教室名称
     * @param string $building 楼号
     * @param string $area 校区
     * @param string $equipment 设施
     * @param string $school  学院
     * @param string $status 可用状态
     * @param string $reserved 保留状态
     * @param int $seatmin 最小座位数
     * @param int $seatmax 最大座位数
     * @param int $testmin 最小考位数
     * @param int $testmax  最大考位数
     * @return array|null
     */
    function getList($page=1,$rows=20,$roomno='%',$name='%',$building='%',$area='',$equipment='',
                     $school='',$status='',$reserved='',$seatmin=0,$seatmax=1000,$testmin=0,$testmax=1000){
        $result=null;
        $condition=null;
        if($roomno!='%') $condition['classrooms.roomno']=array('like',$roomno);
        if($name!='%') $condition['classrooms.jsn']=array('like',$name);
        if($building!='%') $condition['classrooms.building']=array('like',$building);
        if($area!='') $condition['classrooms.area']=$area;
        if($equipment!='') $condition['classrooms.equipment']= $equipment;
        if($school!='') $condition['classrooms.priority']= $school;
        if($status!='') $condition['classrooms.status']= $status;
        if($reserved!='') $condition['classrooms.reserved']= $reserved;
        $condition['seats']= array(array('egt',$seatmin),array('elt',$seatmax));
        $condition['testers']= array(array('egt',$testmin),array('elt',$testmax));
        $data=$this->query->table('classrooms')->join('roomoptions ',' roomoptions.name=classrooms.equipment')
            ->join('schools ',' schools.school=classrooms.priority')
            ->join('areas ',' areas.name=classrooms.area')
            ->join('roomusages ',' roomusages.name=classrooms.usage')
            ->page($page,$rows)
            ->field("roomno,area,rtrim(areas.value) areaname,rtrim(building) building,rtrim(no) no,rtrim(jsn) roomname,seats,testers,equipment,
            rtrim(roomoptions.value) equipmentname,priority school ,rtrim(schools.name ) schoolname,reserved,status,
            usage,rtrim(roomusages.value) usagename")
            ->where($condition)->order('roomno')->select();
        $count= $this->query->table('classrooms')->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
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
     * @return array|null
     * @throws \think\Exception
     */
    function getUsageList($page=1,$rows=20,$year='',$term='',$roomno='%',$name='%',$building='%',$area='',$equipment='',
                           $school='',$seatmin=0,$seatmax=1000,$weekday='',$oew='',$time=''){
        $result=null;
        $condition=null;
        if($year==''||$term=='')
            throw new Exception('year or term is empty', MyException::PARAM_NOT_CORRECT);

        if($weekday=='')
            $daystring='(isnull(mon,0)|isnull(tue,0)|isnull(wes,0)|isnull(thu,0)|isnull(fri,0)|isnull(sat,0)|isnull(sun,0))';
        else
            $daystring=$weekday;

        if($oew!=''){
            $oewobj=new OEW();
            $oewstring=$oewobj->getOEWItem($oew)['timebit'];
        }
        else{
            $oewstring='0';
        }
        if($time!=''){
            $timeobj=new TimeSection();
            $timestring=$timeobj->getTimeSectionItem($time)['timebits2'];
        }
        else{
            $timestring='0';
        }
        $section='~((~isnull('.$daystring.',0))|(~('.$timestring.'&'.$oewstring.')))=0';
        if($roomno!='%') $condition['classrooms.roomno']=array('like',$roomno);
        if($name!='%') $condition['classrooms.jsn']=array('like',$name);
        if($building!='%') $condition['classrooms.building']=array('like',$building);
        if($area!='') $condition['classrooms.area']=$area;
        if($equipment!='') $condition['classrooms.equipment']= $equipment;
        if($school!='') $condition['classrooms.priority']= $school;
        $condition['seats']= array(array('egt',$seatmin),array('elt',$seatmax));
        $condition2['timelist.year']=$year;
        $condition2['timelist.term']=$term;
        $condition2['timelist.type']='R';
        $subsql = Db::table('timelist')->where($condition2)->field('*')->buildSql();
        $data=$this->query->table('classrooms')->join('roomoptions ',' roomoptions.name=classrooms.equipment')
            ->join('areas ',' areas.name=classrooms.area')
            ->join($subsql.'  t ',' t.who=classrooms.roomno','LEFT')
            ->page($page,$rows)
            ->field("roomno,area,rtrim(areas.value) areaname,rtrim(building) building,rtrim(no) no,rtrim(jsn) name,seats,testers,equipment,
            rtrim(roomoptions.value) equipmentname,isnull(mon,0) mon,isnull(tue,0) tue,isnull(wes,0) wes,isnull(thu,0) thu,isnull(fri,0) fri,
            isnull(sat,0) sat,isnull(sun,0) sun")
            ->where($condition)->where($section)->order('roomno')->select();
        $count= $this->query->table('classrooms') ->join($subsql.'  t ',' t.who=classrooms.roomno','LEFT')->where($condition)->where($section)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

    /**刷新教室资源时间表
     * @param $year
     * @param $term
     * @return bool
     * @throws Exception
     * @throws \Exception
     */
    function refreshTimeList($year,$term){
        $this->query->startTrans();
        try {
            $condition=null;
            $condition['timelist.year']=$year;
            $condition['timelist.term']=$term;
            $this->query->table('timelist')->where($condition)->where("type='R'")->delete();//清除原有记录。
            $condition=null;
            $condition['schedule.year']=$year;
            $condition['schedule.term']=$term;
            $subsql_mon = Db::table('schedule')->join('oewoptions ',' oewoptions.code=schedule.oew')
                ->join('timesections ',' timesections.name=schedule.time')
                ->field('schedule.roomno,dbo.group_or(timesections.timebits2&oewoptions.timebit) mon')
                ->where($condition)->group('schedule.roomno')->where('schedule.day=1')->buildSql();
            $subsql_tue = Db::table('schedule')->join('oewoptions ',' oewoptions.code=schedule.oew')
                ->join('timesections ',' timesections.name=schedule.time')
                ->field('schedule.roomno,dbo.group_or(timesections.timebits2&oewoptions.timebit) tue')
                ->where($condition)->group('schedule.roomno')->where('schedule.day=2')->buildSql();
            $subsql_wes = Db::table('schedule')->join('oewoptions ',' oewoptions.code=schedule.oew')
                ->join('timesections ',' timesections.name=schedule.time')
                ->field('schedule.roomno,dbo.group_or(timesections.timebits2&oewoptions.timebit) wes')
                ->where($condition)->group('schedule.roomno')->where('schedule.day=3')->buildSql();
            $subsql_thu = Db::table('schedule')->join('oewoptions ',' oewoptions.code=schedule.oew')
                ->join('timesections ',' timesections.name=schedule.time')
                ->field('schedule.roomno,dbo.group_or(timesections.timebits2&oewoptions.timebit) thu')
                ->where($condition)->group('schedule.roomno')->where('schedule.day=4')->buildSql();
            $subsql_fri = Db::table('schedule')->join('oewoptions ',' oewoptions.code=schedule.oew')
                ->join('timesections ',' timesections.name=schedule.time')
                ->field('schedule.roomno,dbo.group_or(timesections.timebits2&oewoptions.timebit) fri')
                ->where($condition)->group('schedule.roomno')->where('schedule.day=5')->buildSql();
            $subsql_sat = Db::table('schedule')->join('oewoptions ',' oewoptions.code=schedule.oew')
                ->join('timesections ',' timesections.name=schedule.time')
                ->field('schedule.roomno,dbo.group_or(timesections.timebits2&oewoptions.timebit) sat')
                ->where($condition)->group('schedule.roomno')->where('schedule.day=6')->buildSql();
            $subsql_sun = Db::table('schedule')->join('oewoptions ',' oewoptions.code=schedule.oew')
                ->join('timesections ',' timesections.name=schedule.time')
                ->field('schedule.roomno,dbo.group_or(timesections.timebits2&oewoptions.timebit) sun')
                ->where($condition)->group('schedule.roomno')->where('schedule.day=7')->buildSql();
            $condition=null;
            $condition['schedule.year']=$year;
            $condition['schedule.term']=$term;
            $this->query->table('schedule')
                ->join($subsql_mon.' t1','t1.roomno=schedule.roomno','LEFT')
                ->join($subsql_tue.' t2','t2.roomno=schedule.roomno','LEFT')
                ->join($subsql_wes.' t3','t3.roomno=schedule.roomno','LEFT')
                ->join($subsql_thu.' t4','t4.roomno=schedule.roomno','LEFT')
                ->join($subsql_fri.' t5','t5.roomno=schedule.roomno','LEFT')
                ->join($subsql_sat.' t6','t6.roomno=schedule.roomno','LEFT')
                ->join($subsql_sun.' t7','t7.roomno=schedule.roomno','LEFT')
                ->field("distinct schedule.year,schedule.term, schedule.roomno,'R'as type,'' as para,isnull(t1.mon,0) mon,isnull(t2.tue,0) tue,isnull(t3.wes,0) wes
            ,isnull(t4.thu,0) thu,isnull(t5.fri,0) fri,isnull(t6.sat,0) sat,isnull(t7.sun,0) sun")
                ->where($condition)->selectInsert('year,term,who,type,para,mon,tue,wes,thu,fri,sat,sun','timelist');
        }
        catch(Exception $e){
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        return true;
    }
} 