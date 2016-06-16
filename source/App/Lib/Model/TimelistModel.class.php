<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/28
 * Time: 15:47
 */
class TimelistModel extends CommonModel{


    /**
     * 根据排课计划的教室安排刷新教室的时间表
     * @param int $year
     * @param int $term
     * @param int $day
     * @param string $roomno
     * @return int|string
     * @throws Exception
     */
    public function updateRoomScheduleTimelist($year,$term,$day,$roomno='%'){
        $fieldname = $this->dayInt2String($day);
        $sql = "
update TIMELIST SET {$fieldname}=C.TT from TIMELIST INNER JOIN (

select B.TT,T.* from TIMELIST T INNER JOIN (
	select
SCHEDULE.ROOMNO,
dbo.group_or(OEWOPTIONS.TIMEBIT&TIMESECTIONS.TIMEBITS2) AS TT,
dbo.getone(SCHEDULE.[YEAR]) as [YEAR],
dbo.getone(SCHEDULE.[TERM]) as [TERM],
dbo.getone(SCHEDULE.[DAY]) as [DAY]
	from SCHEDULE
	INNER JOIN OEWOPTIONS on SCHEDULE.OEW=OEWOPTIONS.CODE
	INNER JOIN TIMESECTIONS ON  SCHEDULE.TIME=TIMESECTIONS.NAME
	INNER JOIN TIMELIST t1 ON t1.WHO=SCHEDULE.ROOMNO AND t1.YEAR=SCHEDULE.YEAR AND t1.TERM=SCHEDULE.TERM
	where SCHEDULE.[YEAR]=:year
	AND SCHEDULE.TERM=:term
	and SCHEDULE.[DAY]= {$day}
	group by ROOMNO
)AS B ON T.WHO=B.ROOMNO AND T.YEAR=B.year AND T.TERM=B.term
) AS C ON  C.WHO=TIMELIST.WHO AND C.YEAR=TIMELIST.YEAR AND C.TERM=TIMELIST.TERM
where TIMELIST.WHO like :roomno ";
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':roomno'   => $roomno
        );
        return $this->doneExecute($this->sqlExecute($sql,$bind));
    }


}