<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/22
 * Time: 13:38
 */
class RoomModel extends CommonModel{

    /**
     * 获取教室
     * @param array $whr 查询条件，书写规则见Model::makeSegments注释
     * @param bool $count true表示只获取其数量
     * @return array|int|string
     */
    public function get($whr=null,$count=false){
        return $this->getTable('CLASSROOMS',$whr,$count);
    }

    /**
     * 根据教室号获取教室信息
     * @param $roomno
     * @return string
     */
    public function getByRoomno($roomno){
        $rst = $this->getTable('CLASSROOMS',array(
            'ROOMNO'    => $roomno,
        ));
        if(is_string($rst) or !$rst){
            return "查询教室号为[$roomno]的教室信息失败！{$rst}";
        }else{
            return $rst[0];
        }
    }

    /**
     * 添加教室
     * @param mixed $fields 字段数组
     * @return int|mixed|string
     */
    public function add($fields){
        return $this->createRecord('classrooms',$fields);
    }

    /**
     * 删除教室
     * @param array|mixed $fields
     * @return int|mixed|string
     */
    public function delete($fields){
        return $this->deleteRecords('CLASSROOMS',$fields);
    }

    /**
     * 修改教室
     * @param array $fields 更新字段
     * @param array $where
     * @return int|string
     */
    public function update($fields,$where){
        return $this->updateRecords('CLASSROOMS',$fields,$where);
    }

    /**
     * 更具教室号删除教室
     * @param $roomno
     * @return int|string
     */
    public function deleteByRoomno($roomno){
        return $this->deleteRecords('CLASSROOMS',array(
            'ROOMNO'    => $roomno,
        ));
    }

    /**
     * 增加教室申请记录
     * @param $fields
     * @return int|string
     */
    public function addBorrowRecord($fields){
        return $this->createRecord('roomreserve',$fields);
    }

    /**
     * 获取教室申请记录
     * @param $recno
     * @return array|int|string
     */
    public function getBorrowedRecordByRecno($recno){
        return $this->getTable('roomreserve',array(
            'RECNO' => $recno
        ));
    }

    /**
     * 使用recno修改教室借用审批记录
     * @param $recno
     * @param bool $pass
     * @return int|string
     */
    public function updateBorrowVerifiedStateByRecno($recno,$pass=true){
        return $this->updateBorrowRecord(array(
            'APPROVED'  => $pass?1:0,
        ),array(
            'RECNO'     => $recno,
        ));
    }

    /**
     * 获取教室批准单数据
     * @param $recno
     * @return array|string
     */
    public function getConfirmationSheetDataByRecno($recno){
        $fields = '
RTRIM(CLASSROOMS.BUILDING) as BUILDING,
RTRIM(CLASSROOMS.JSN) as JSN,
ROOMRESERVE.ROOMNO,
ROOMRESERVE.DAY,
ROOMRESERVE.WEEKS,
RTRIM(ROOMRESERVE.PURPOSE) as PURPOSE,
OEWOPTIONS.NAME AS OEW,
CONVERT(varchar(10),GETDATE(),20) AS ISSUEDATE,
RTRIM(SCHOOLS.NAME) AS SCHOOL,
TIMESECTIONS.VALUE AS TIME,
ROOMRESERVE.YEAR AS YEAR,
ROOMRESERVE.TERM AS TERM';
        $join = '
INNER JOIN CLASSROOMS ON ROOMRESERVE.ROOMNO=CLASSROOMS.ROOMNO
INNER JOIN OEWOPTIONS ON ROOMRESERVE.OEW=OEWOPTIONS.CODE
INNER JOIN SCHOOLS ON ROOMRESERVE.SCHOOL=SCHOOLS.SCHOOL
LEFT OUTER JOIN TIMESECTIONS ON ROOMRESERVE.TIME=TIMESECTIONS.NAME';
        $where = 'ROOMRESERVE.RECNO=:RECNO AND ROOMRESERVE.APPROVED=1';
        $sql = $this->makeSql('ROOMRESERVE',array(
            'fields'    => $fields,
            'join'      => $join,
            'where'     => $where,
        ));
        return $this->doneQuery($this->sqlQuery($sql,array(
            ':RECNO'    => $recno,
        )),false);
    }

    /**
     * 删除教室申请记录
     * @param $recno
     * @return int|string
     */
    public function deleteBorrowRecordByRecno($recno){
        return $this->deleteRecords('roomreserve',array(
            'RECNO' => $recno,
        ));
    }
    /**
     * 更新教室申请记录
     * @param $fields
     * @param $where
     * @return int|string
     */
    public function updateBorrowRecord($fields,$where){
        return $this->updateRecords('roomreserve',$fields,$where);
    }

    /**
     * 获取查询教室的表格数据
     * @param $params
     * @param $offset
     * @param $limit
     * @return array|string
     */
    public function getRoomsTableList($params = null,$offset=null,$limit=null){
        if(!isset($params)){
            $params = $_POST;
        }
        $bind = array(
            ':ROOMNO'=>doWithBindStr($params['ROOMNO']),
            ':AREA'=>doWithBindStr($params['AREA']),
            ':BUILDING'=>doWithBindStr($params['BUILDING']),
            ':EQUIPMENT'=>doWithBindStr($params['EQUIPMENT']),
            ':NO'=>doWithBindStr($params['NO']),
            ':PRIORITY'=>doWithBindStr($params['PRIORITY']),
            ':SEATSDOWN'=>$params['SEATSDOWN'],
            ':SEATSUP'=>$params['SEATSUP'],
            ':TESTERSDOWN'=>$params['TESTERSDOWN'],
            ':TESTERSUP'=>$params['TESTERSUP'],
            ':USAGE'=>doWithBindStr($params['USAGE']),
        );
        $reservepart = $statuspart = '';
        if($params['RESERVED'] !== '%'){
            $reservepart = 'AND Dbo_classrooms.RESERVED = :RESERVED';
            $bind[':RESERVED'] = $params['RESERVED'];
        }
        if($params['STATUS'] !== '%'){
            $statuspart = 'AND Dbo_classrooms.STATUS = :STATUS';
            $bind[':STATUS'] = $params['STATUS'];
        }
        $fields = '
Dbo_classrooms.ROOMNO,
Dbo_classrooms.NO,
Dbo_classrooms.JSN,
Dbo_classrooms.BUILDING,
Dbo_classrooms.AREA,
Dbo_classrooms.SEATS,
Dbo_classrooms.TESTERS,
Dbo_classrooms.EQUIPMENT,
Dbo_classrooms.STATUS,
Dbo_classrooms.PRIORITY,
Dbo_classrooms.USAGE,
Dbo_classrooms.RESERVED,
Dbo_areas.VALUE AS AREAVALUE,
Dbo_roomoptions.VALUE AS EQUIPMENTVALUE,
Dbo_roomusages.VALUE AS USAGEVALUE,
Dbo_schools.NAME AS SCHOOLNAME,
Dbo_classrooms.REM';
        $join = '
LEFT OUTER JOIN dbo.AREAS Dbo_areas on Dbo_classrooms.AREA = Dbo_areas.NAME
LEFT OUTER JOIN dbo.ROOMOPTIONS Dbo_roomoptions on Dbo_classrooms.EQUIPMENT = Dbo_roomoptions.NAME
LEFT OUTER JOIN dbo.ROOMUSAGES Dbo_roomusages on Dbo_classrooms.USAGE = Dbo_roomusages.NAME
LEFT OUTER JOIN dbo.SCHOOLS Dbo_schools on Dbo_classrooms.PRIORITY = Dbo_schools.SCHOOL';
        $where = "
Dbo_classrooms.ROOMNO LIKE :ROOMNO
   AND  Dbo_classrooms.AREA LIKE :AREA
   AND  Dbo_classrooms.BUILDING LIKE :BUILDING
   AND  Dbo_classrooms.EQUIPMENT LIKE :EQUIPMENT
   AND  Dbo_classrooms.NO LIKE :NO
   AND  Dbo_classrooms.PRIORITY LIKE :PRIORITY
   AND  (Dbo_classrooms.SEATS >= :SEATSDOWN  AND  Dbo_classrooms.SEATS <= :SEATSUP)
   AND  ( Dbo_classrooms.TESTERS >= :TESTERSDOWN  AND  Dbo_classrooms.TESTERS <= :TESTERSUP)
   AND  Dbo_classrooms.USAGE LIKE :USAGE
   $reservepart
   $statuspart
   ";
        $order = 'Dbo_classrooms.NO';
        $csql = $this->makeCountSql('dbo.CLASSROOMS Dbo_classrooms',array(
            'join'      => $join,
            'where'     => $where,
        ));
        $ssql = $this->makeSql('dbo.CLASSROOMS Dbo_classrooms',array(
            'fields'    => $fields,
            'join'      => $join,
            'where'     => $where,
            'order'      => $order,
        ),$offset,$limit);
        return $this->getTableList($csql,$ssql,$bind);
    }

    /**
     * 获取教室借用情况管理列表数据
     * @param $bind
     * @param $filter
     * @param null $offset
     * @param null $limit
     * @return array|string
     */
    public function getBorrowedTableList($bind,$filter,$offset=null,$limit=null){
        $fields = '
RECNO,ROOMNO,JSN,EQUIPMENT,STATUS,RESERVED,DAY,TIME,OEW,WEEKS,SCHOOL,
CONVERT(varchar(20),APPLYDATE,20) AS APPLYDATE,
USERNAME,APPROVED,PURPOSE';
        $where = "
YEAR= :YEAR
AND TERM= :TERM
AND SCHOOLCODE LIKE :SCHOOL
AND ROOMNO LIKE :ROOMNO
$filter ";
        $order = " APPLYDATE ";
        $csql = $this->makeCountSql('VIEWROOMLEASE',array(
            'where' => $where,
        ));
        $ssql = $this->makeSql('VIEWROOMLEASE',array(
            'fields'    => $fields,
            'where' => $where,
            'order' => $order,
        ),$offset,$limit);
        return $this->getTableList($csql,$ssql,$bind);
    }

    public function getRoomTimeTableList($bind,$offset=null,$limit=null){
        $fields = '
ISNULL(TIMELIST.MON,0) AS MON,
ISNULL(TIMELIST.TUE,0) AS TUE,
ISNULL(TIMELIST.WES,0) AS WES,
ISNULL(TIMELIST.THU,0) AS THU,
ISNULL(TIMELIST.FRI,0) AS FRI,
ISNULL(TIMELIST.SAT,0) AS SAT,
ISNULL(TIMELIST.SUN,0) AS SUN,
CLASSROOMS.ROOMNO,
CLASSROOMS.JSN,
CLASSROOMS.SEATS,
CLASSROOMS.TESTERS,
CLASSROOMS.RESERVED';
        $join = 'LEFT OUTER JOIN TIMELIST ON CLASSROOMS.ROOMNO=TIMELIST.WHO';
        $where = "
--未完成
--(ISNULL(TIMELIST.MON,0) & 0 =0)
--AND (ISNULL(TIMELIST.TUE,0) & 0 =0)
--AND (ISNULL(TIMELIST.WES,0) & 0 =0)
--AND (ISNULL(TIMELIST.THU,0) & 0 =0)
--AND (ISNULL(TIMELIST.FRI,0) & 0 =0)
--AND (ISNULL(TIMELIST.SAT,0) & 0 =0)
--AND (ISNULL(TIMELIST.SUN,0) & 0 =0)
--AND 
 CLASSROOMS.ROOMNO LIKE :ROOMNO
AND CLASSROOMS.JSN LIKE :JSN
AND CLASSROOMS.PRIORITY LIKE :SCHOOL
AND CLASSROOMS.EQUIPMENT LIKE :EQUIPMENT
AND CLASSROOMS.AREA LIKE :AREA
AND CLASSROOMS.SEATS >=:SEATSDOWN
AND CLASSROOMS.SEATS <=:SEATSUP
AND TIMELIST.YEAR=:YEAR
AND TIMELIST.TERM=:TERM
AND TIMELIST.TYPE='R' ";
        $csql = $this->makeCountSql('CLASSROOMS',array(
            'join'  => $join,
            'where' => $where
        ));
        $ssql = $this->makeSql('CLASSROOMS',array(
            'fields'    => $fields,
            'join'  => $join,
            'where' => $where
        ),$offset,$limit);
        return $this->getTableList($csql,$ssql,$bind);
    }

    /**
     * 根据学年学期教室号
     *  获取教室借用记录
     * @param $year
     * @param $term
     * @param $roomno
     * @return array|string
     */
    public function getRoomBorrowRecordList($year,$term,$roomno){
        $sql = "
SELECT
ROOMRESERVE.TIME,
ROOMRESERVE.DAY,
ROOMRESERVE.OEW,
RTRIM(SCHOOLS.NAME)+' 借用' AS Message,--借用学院
ROOMRESERVE.WEEKS AS WEEKS, --周次
ROOMRESERVE.ROOMNO
FROM ROOMRESERVE
JOIN SCHOOLS ON ROOMRESERVE.SCHOOL=SCHOOLS.SCHOOL
WHERE ROOMRESERVE.YEAR=:year
AND ROOMRESERVE.TERM=:term
AND ROOMRESERVE.ROOMNO=:roomno
AND ROOMRESERVE.APPROVED=1
ORDER BY [TIME]";
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':roomno' => $roomno,
        );
        return $this->doneQuery($this->sqlQuery($sql,$bind));
    }
    /**
     * 根据学年学期教室号
     *  获取排课中教室使用记录
     * 注意：
     *  改学年学期课程号组号对应多个班级，则班级会分不同记录显示
     *  相比旧的SQL会多出几条记录
select
SCHEDULE.TIME+SCHEDULE.DAY+SCHEDULE.OEW AS TIME,--节次 + 周几 + 单双周标记
--课程 + 课程号 + [教师号]
CASE WHEN TEACHERS.NAME  is NULL    THEN RTRIM(COURSES.COURSENAME)+':'+SCHEDULE.COURSENO+SCHEDULE.[GROUP]
WHEN TEACHERS.NAME  is not NULL THEN RTRIM(COURSES.COURSENAME)+':'+SCHEDULE.COURSENO+SCHEDULE.[GROUP]+'('+RTRIM(TEACHERS.NAME) +')'
end as COURSE,
SCHEDULE.COURSENO AS COURSENO,
SCHEDULE.[GROUP] AS COURSEGROUP,
SCHEDULE.WEEKS AS WEEKS
FROM SCHEDULE
INNER JOIN COURSES     		ON SCHEDULE.COURSENO=COURSES.COURSENO
INNER JOIN TEACHERPLAN 		ON SCHEDULE.MAP=TEACHERPLAN.RECNO
LEFT OUTER JOIN TEACHERS 	ON TEACHERPLAN.TEACHERNO=TEACHERS.TEACHERNO
WHERE SCHEDULE.YEAR=2014
AND SCHEDULE.TERM=2
AND SCHEDULE.ROOMNO='00B108060'
ORDER BY TIME
     * @param $year
     * @param $term
     * @param $roomno
     * @return array|string
     */
    public function getRoomScheduleRecordList($year,$term,$roomno){
        $sql = "
select
--节次 + 周几 + 单双周标记
SCHEDULE.TIME,
SCHEDULE.DAY,
SCHEDULE.OEW,
--课程 + 课程号 + [教师号]
CASE WHEN TEACHERS.NAME  is NULL    THEN RTRIM(COURSES.COURSENAME)+':'+SCHEDULE.COURSENO+SCHEDULE.[GROUP]
		WHEN TEACHERS.NAME  is not NULL THEN RTRIM(COURSES.COURSENAME)+':'+SCHEDULE.COURSENO+SCHEDULE.[GROUP]+'('+RTRIM(TEACHERS.NAME) +')'
	end as Message,
SCHEDULE.COURSENO+SCHEDULE.[GROUP] AS COURSENO,
SCHEDULE.WEEKS AS WEEKS,
SCHEDULE.ROOMNO,
dbo.GROUP_CONCAT(RTRIM(CLASSES.CLASSNAME),',') as classname
FROM SCHEDULE
INNER JOIN COURSEPLAN ON SCHEDULE.[YEAR] = COURSEPLAN.[YEAR] AND SCHEDULE.[TERM] = COURSEPLAN.[TERM] AND
		SCHEDULE.[COURSENO] = COURSEPLAN.[COURSENO] AND SCHEDULE.[GROUP] = COURSEPLAN.[GROUP]
INNER JOIN CLASSES          ON CLASSES.CLASSNO = COURSEPLAN.CLASSNO
INNER JOIN COURSES     		ON SCHEDULE.COURSENO=COURSES.COURSENO
INNER JOIN TEACHERPLAN 		ON SCHEDULE.MAP=TEACHERPLAN.RECNO
LEFT OUTER JOIN TEACHERS 	ON TEACHERPLAN.TEACHERNO=TEACHERS.TEACHERNO
WHERE SCHEDULE.YEAR=:year
AND SCHEDULE.TERM=:term
AND SCHEDULE.ROOMNO=:roomno
GROUP BY SCHEDULE.[TIME],SCHEDULE.DAY,SCHEDULE.OEW,TEACHERS.NAME,COURSES.COURSENAME,TEACHERS.NAME,
SCHEDULE.COURSENO,SCHEDULE.[GROUP],SCHEDULE.WEEKS,SCHEDULE.ROOMNO";
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':roomno' => $roomno,
        );
        return $this->doneQuery($this->sqlQuery($sql,$bind));
    }


    /**
     * 从借用表中找到借用教室条数
     * @param int $year 学年
     * @param int $term 学期
     * @param string $roomno 教室号
     * @param string $week 星期几
     * @param string $weeks 周次
     * @param string $times 上课节次
     * @return int|string
     */
    function countUseRoomByBorrow($year, $term, $roomno, $week, $weeks, $times){
        $sql = "SELECT count(*) FROM ROOMRESERVE
                LEFT JOIN TIMESECTORS ON TIMESECTORS.NAME=ROOMRESERVE.[TIME]
                WHERE ROOMRESERVE.YEAR=:YEAR AND ROOMRESERVE.TERM=:TERM AND ROOMRESERVE.ROOMNO=:ROOMNO
                AND ROOMRESERVE.DAY=:WEEKDAY AND (
                  ROOMRESERVE.TIME='%' OR ((ROOMRESERVE.WEEKS & :WEEKS)>0 AND (TIMESECTORS.TIMEBITS & :TIMES) >0)
               )
               AND ROOMRESERVE.APPROVED=1";
        $bind = array(
            ":YEAR" => $year,
            ":TERM" => $term,
            ":ROOMNO" => $roomno,
            ":WEEKDAY" => $week,
            ":WEEKS" => $weeks,
            ":TIMES" => $times
        );
        return $this->sqlCount($sql, $bind);
    }

    /**
     * 从排课表中找到借用教室条数
     * @param int $year 学年
     * @param int $term 学期
     * @param string $roomno 教室号
     * @param string $week 星期几
     * @param string $weeks 周次
     * @param string $times 上课节次
     * @param string $oew 单双周
     * @return int|string
     */
    function countUseRoomBySchedule($year, $term, $roomno, $week, $weeks, $times, $oew='B'){
        //单双周 1111 1111 1111 1111 1111 => 1048575
        //单周   0101 0101 0101 0101 0101 => 349525
        //双周   1010 1010 1010 1010 1010 => 699050
        $bit = array("B"=>1048575,"O"=>349525,"E"=>699050);
        $sql = "select count(*) FROM SCHEDULE
              INNER JOIN TIMESECTORS ON TIMESECTORS.NAME=SCHEDULE.[TIME]
              WHERE SCHEDULE.YEAR=:YEAR AND SCHEDULE.TERM=:TERM AND SCHEDULE.ROOMNO=:ROOMNO
              AND SCHEDULE.DAY=:WEEKDAY AND (SCHEDULE.WEEKS & (CASE WHEN SCHEDULE.OEW='O' THEN 349525 WHEN SCHEDULE.OEW='E' THEN 699050 ELSE 1048575 END) & :WEEKS) > 0
              AND (TIMESECTORS.TIMEBITS & :TIMES) >0";
        $bind = array(
            ":YEAR" => $year,
            ":TERM" => $term,
            ":ROOMNO" => $roomno,
            ":WEEKDAY" => $week,
            //":BITS"=> isset($bit[$oew]) ? $bit[$oew] : $bit["B"] ,
            ":WEEKS" => $weeks,
            ":TIMES" => $times
        );
        return $this->sqlCount($sql, $bind);
    }

    /**
     * 罗列教室列表
     * @param string $roomno
     * @param string $roomname
     * @param null $offset
     * @param null $limit
     * @return array|string
     */
    public function listRooms($roomno='%',$roomname='%',$offset=null,$limit=null){
        $fields = '
ROOMNO as roomno,
TESTERS as testers, -- 考试作为数
RTRIM(BUILDING) as building,
[NO] as [no], -- 房间门牌号
RTRIM(JSN) as roomname ';
        $where = 'ROOMNO LIKE :roomno AND ISNULL(JSN,\'\') LIKE :roomname and status = 1';
        $order = 'testers desc';
        $csql = $this->makeCountSql('CLASSROOMS',array(
            'where'     => $where,
        ));
        $ssql = $this->makeSql('CLASSROOMS',array(
            'fields'    => $fields,
            'where'     => $where,
            'order'     => $order,
        ),$offset,$limit);
        $bind = array(
            ':roomno'   => $roomno,
            ':roomname' => $roomname,
        );
        $list = $this->getTableList($csql,$ssql,$bind);

//        mist($csql,$ssql,$bind);

        return $list;
    }

    /**
     * 获取可用的教室列表
     * @return array|string
     */
    public function getUsableRoomlist(){
        $sql = '
SELECT
[ROOMNO] as roomno,
[AREA] as area,
[BUILDING] as building,
[NO] as [no],
RTRIM([JSN]) as jsn,
[SEATS] as seats,
[TESTERS] as testers,
[EQUIPMENT] as equipment,
[PRIORITY] as priority,
[USAGE] as [usage],
RTRIM([REM]) as rem,
[RESERVED] as reserved
FROM CLASSROOMS
WHERE STATUS = 1 and RESERVED = 0';
        return $this->doneQuery($this->sqlQuery($sql));
    }

}