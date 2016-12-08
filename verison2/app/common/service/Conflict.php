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
// | Created:2016/11/25 12:21
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyAccess;
use app\common\access\MyService;
use think\Db;

class Conflict extends MyService {
    function getList($page=1,$rows=20,$year,$term)
    {
        $result = ['total' => 0, 'rows' => []];
        $condition = null;
        $condition['year']=$year;
        $condition['term']=$term;
        $data = $this->query->table('conflict')->page($page, $rows)
            ->join('timesections','timesections.timebits=conflict.time','LEFT')
            ->join('whotype','whotype.who=conflict.type')
            ->field('year,term,rtrim(conflict.who) who,conflict.type,rtrim(whotype.name) typename,day,time,rtrim(timesections.value) timename,week,rtrim(conflict.name) name')
            ->where($condition)->order('type,who')->select();
        $count = $this->query->table('conflict')->where($condition)->count();
        if (is_array($data) && count($data) > 0)
            $result = array('total' => $count, 'rows' => $data);
        return $result;
    }

    function init($year,$term){
        $row=0;
        try {
            MyAccess::checkAccess('E');
            $bind=["year"=>$year,"term"=>$term];
            //删除原有记录
            $sql='delete from conflict
                where year=:year and term=:term';
            Db::execute($sql,$bind);
            //教师部分 完全重复
            $sql="insert into conflict(year,term,type,who,day,week,time)
                select schedule.year,schedule.term,'T',teacherno,day,weeks&OEWOPTIONS.TIMEBIT2 week,TIMESECTIONS.TIMEBITS
                from schedule inner join teacherplan on teacherplan.recno=schedule.map
                inner join oewoptions on OEWOPTIONS.code=schedule.oew
                inner join TIMESECTIONS on TIMESECTIONS.name=SCHEDULE.time
                where teacherno!='000000' and day!=0 and schedule.year=:year and schedule.term=:term
                group by schedule.year,schedule.term,teacherno,day,weeks&OEWOPTIONS.TIMEBIT2,time,TIMESECTIONS.TIMEBITS
                having count(*)>1";
            $row=Db::execute($sql,$bind);
            //某些周次重复部分
            $sql="insert into conflict(year,term,type,who,day,week,time)
                select year,term,type,teacherno,day,dbo.GROUP_AND(week) as week,time from (
                select schedule.year,schedule.term,'T' type,teacherno,day,weeks&OEWOPTIONS.TIMEBIT2 week,TIMESECTIONS.TIMEBITS time
                from schedule inner join teacherplan on teacherplan.recno=schedule.map
                inner join oewoptions on OEWOPTIONS.code=schedule.oew
                inner join TIMESECTIONS on TIMESECTIONS.name=SCHEDULE.time
                where teacherno!='000000' and day!=0 and schedule.year=:year and schedule.term=:term
                group by schedule.year,schedule.term,teacherno,day,weeks&OEWOPTIONS.TIMEBIT2,time,TIMESECTIONS.TIMEBITS
                having count(*)=1 ) as t
                group by year,term,type,teacherno,day,time
                having count(*)>1 and dbo.GROUP_AND(week)>0";
            $row+=Db::execute($sql,$bind);
            //完全重复 教室
            $sql="insert into conflict(year,term,type,who,day,week,time)
                select schedule.year,schedule.term,'R',roomno,day,weeks&OEWOPTIONS.TIMEBIT2 week,TIMESECTIONS.TIMEBITS
                from (select distinct year,term,courseno,[group],day,time,roomno,weeks,oew from schedule ) as schedule
                inner join oewoptions on OEWOPTIONS.code=schedule.oew
                inner join TIMESECTIONS on TIMESECTIONS.name=SCHEDULE.time
                where roomno!='000000000' and day!=0 and schedule.year=:year and schedule.term=:term
                group by schedule.year,schedule.term,roomno,day,weeks&OEWOPTIONS.TIMEBIT2,time,TIMESECTIONS.TIMEBITS
                having count(*)>1";
            $row+=Db::execute($sql,$bind);
            //某些周次重复部分
            $sql="insert into conflict(year,term,type,who,day,week,time)
                select year,term,type,roomno,day,dbo.GROUP_AND(week) as week,time from (
                select schedule.year,schedule.term,'R' type,roomno,day,weeks&OEWOPTIONS.TIMEBIT2 week,TIMESECTIONS.TIMEBITS time
                from (select distinct year,term,courseno,[group],day,time,roomno ,weeks,oew from schedule ) schedule
                inner join oewoptions on OEWOPTIONS.code=schedule.oew
                inner join TIMESECTIONS on TIMESECTIONS.name=SCHEDULE.time
                where roomno!='000000000' and day!=0 and schedule.year=:year and schedule.term=:term
                group by schedule.year,schedule.term,roomno,day,weeks&OEWOPTIONS.TIMEBIT2,time,TIMESECTIONS.TIMEBITS
                having count(*)=1 ) as t
                group by year,term,type,roomno,day,time
                having count(*)>1 and dbo.GROUP_AND(week)>0";
            $row+=Db::execute($sql,$bind);

            //完全重复 班级
            $sql="insert into conflict(year,term,type,who,day,week,time)
                select schedule.year,schedule.term,'C',classno,day,schedule.weeks&OEWOPTIONS.TIMEBIT2 week,TIMESECTIONS.TIMEBITS
                from (select distinct year,term,courseno,[group],day,time,roomno ,weeks,oew from schedule ) schedule
                inner join courseplan on courseplan.courseno+courseplan.[group]=schedule.courseno+schedule.[group]
                and schedule.year=courseplan.year and schedule.term=courseplan.term
                inner join oewoptions on OEWOPTIONS.code=schedule.oew
                inner join TIMESECTIONS on TIMESECTIONS.name=SCHEDULE.time
                where classno!='000000' and day!=0 and schedule.year=:year and schedule.term=:term
                group by schedule.year,schedule.term,classno,day,schedule.weeks&OEWOPTIONS.TIMEBIT2,time,TIMESECTIONS.TIMEBITS
                having count(*)>1";
            $row+=Db::execute($sql,$bind);
            //某些周次重复部分
            $sql="insert into conflict(year,term,type,who,day,week,time)
                select year,term,type,classno,day,dbo.GROUP_AND(week) as week,time from (
                select schedule.year,schedule.term,'C' type,classno,day,schedule.weeks&OEWOPTIONS.TIMEBIT2 week,TIMESECTIONS.TIMEBITS time
                from (select distinct year,term,courseno,[group],day,time,roomno ,weeks,oew from schedule )  schedule
                inner join courseplan on courseplan.courseno+courseplan.[group]=schedule.courseno+schedule.[group]
                and schedule.year=courseplan.year and schedule.term=courseplan.term
                inner join oewoptions on OEWOPTIONS.code=schedule.oew
                inner join TIMESECTIONS on TIMESECTIONS.name=SCHEDULE.time
                where classno!='000000' and day!=0 and schedule.year=:year  and schedule.term=:term
                group by schedule.year,schedule.term,classno,day,schedule.weeks&OEWOPTIONS.TIMEBIT2,time,TIMESECTIONS.TIMEBITS
                having count(*)=1 ) as t
                group by year,term,type,classno,day,time
                having count(*)>1 and dbo.GROUP_AND(week)>0";
            $row+=Db::execute($sql,$bind);


            $sql="update conflict
                set name=classes.classname
                from conflict inner join classes on classes.classno=conflict.who
                where conflict.year=:year and conflict.term=:term and conflict.type='C'";
            Db::execute($sql,$bind);

            $sql="update conflict
                set name=classrooms.jsn
                from conflict inner join classrooms on classrooms.roomno=conflict.who
                where conflict.year=:year and conflict.term=:term and conflict.type='R'";
            Db::execute($sql,$bind);

            $sql="update conflict
                set name=teachers.name
                from conflict inner join teachers on teachers.teacherno=conflict.who
                where conflict.year=:year and conflict.term=:term and conflict.type='T'";
            Db::execute($sql,$bind);

        }
        catch(\Exception $e){
            throw $e;
        }
        return ["info"=>"检测完成！".$row."条冲突记录","status"=>"1"];
    }
}