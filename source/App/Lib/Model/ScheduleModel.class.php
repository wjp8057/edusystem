<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/28
 * Time: 15:36
 */
class ScheduleModel extends CommonModel{

    /**
     * 获取教室的时间位
     * @param int $year
     * @param int $term
     * @param int $day 1表示星期一
     * @param string $roomno 教室号，默认获取全部
     * @return array|string
     */
    public function getTimebits($year,$term,$day,$roomno='%'){
        $sql = "
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
where SCHEDULE.[YEAR]=:roomno
AND SCHEDULE.TERM=:term
and SCHEDULE.[DAY]=:day
and SCHEDULE.ROOMNO like :roomno
group by SCHEDULE.ROOMNO";
        $bind = array(
            ':year' => $year,
            ':term' => $term,
            ':day'  => $day,
            ':roomno'   => $roomno,
        );
        return $this->doneQuery($this->sqlQuery($sql,$bind));
    }


}