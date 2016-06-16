SELECT distinct RTRIM(VIEWSCHEDULE.COURSENOGROUP) AS COURSENO,
      RTRIM(VIEWSCHEDULE.COURSENAME) AS COURSENAME,
      VIEWSCHEDULE.CREDITS AS CREDITS, RTRIM(VIEWSCHEDULE.COURSETYPE) AS COURSETYPE,
      RTRIM(VIEWSCHEDULE.EXAMTYPE) AS EXAMTYPE,
      SCHEDULEPLAN.estimate AS ESTIMATE,
      isnull(attendamount.amount,0) AS ATTENDAMOUNT,
      RTRIM(VIEWSCHEDULE.COURSETYPENAME) AS COURSETYPENAME,
      RTRIM(VIEWSCHEDULE.SCHOOLNAME) AS SCHOOLNAME,viewschedule.dayntime DAYNTIME,
      scheduleplan.rem as REM
FROM VIEWSCHEDULE LEFT OUTER JOIN
      SCHEDULEPLAN ON VIEWSCHEDULE.RECNO = SCHEDULEPLAN.RECNO left outer join 
     (select count(*) as amount,courseno,[group],year,term 
      from r32 
      where year=:R32YEAR and term=:R32TERM
      group by courseno,[group],year,term ) as attendamount on attendamount.year=scheduleplan.year and attendamount.term=scheduleplan.term and attendamount.courseno=scheduleplan.courseno and attendamount.[group]=scheduleplan.[group]
WHERE VIEWSCHEDULE.YEAR = :YEAR AND VIEWSCHEDULE.TERM =:TERM AND
      (VIEWSCHEDULE.COURSENOGROUP LIKE '08%' ) and SCHEDULEPLAN.estimate>isnull(attendamount.amount,0)
ORDER BY COURSENO