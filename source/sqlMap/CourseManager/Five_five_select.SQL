select * from(select row_number() over(ORDER BY b.kh) as row,b.* from(SELECT distinct RTRIM(VIEWSCHEDULE.COURSENOGROUP) AS kh,
      RTRIM(VIEWSCHEDULE.COURSENAME) AS km,
      VIEWSCHEDULE.CREDITS AS xf, RTRIM(VIEWSCHEDULE.COURSETYPE) AS xk,
      RTRIM(VIEWSCHEDULE.EXAMTYPE) AS kh2,
      SCHEDULEPLAN.estimate AS yjrs,
      isnull(attendamount.amount,0) AS yxrs,
      RTRIM(VIEWSCHEDULE.COURSETYPENAME) AS kclb,
      RTRIM(VIEWSCHEDULE.SCHOOLNAME) AS kkxy,viewschedule.dayntime sksj,
      scheduleplan.rem as bz
FROM VIEWSCHEDULE LEFT OUTER JOIN
      SCHEDULEPLAN ON VIEWSCHEDULE.RECNO = SCHEDULEPLAN.RECNO left outer join
     (select count(*) as amount,courseno,[group],year,term
      from r32
      where year=:YONE and term=:TONE
      group by courseno,[group],year,term ) as attendamount on attendamount.year=scheduleplan.year and attendamount.term=scheduleplan.term
      and attendamount.courseno=scheduleplan.courseno and attendamount.[group]=scheduleplan.[group]
WHERE VIEWSCHEDULE.YEAR = :YTWO AND VIEWSCHEDULE.TERM =:TTWO AND
      (VIEWSCHEDULE.COURSENOGROUP LIKE '08%' ) and SCHEDULEPLAN.estimate>isnull(attendamount.amount,0)
)as b) as c where c.row between :start and :end