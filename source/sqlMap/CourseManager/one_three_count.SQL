select count(*) as ROWS FROM (SELECT distinct RTRIM(VIEWSCHEDULE.COURSENOGROUP) AS 课号,
      RTRIM(VIEWSCHEDULE.COURSENAME) AS 课名,
      VIEWSCHEDULE.CREDITS AS 学分, RTRIM(VIEWSCHEDULE.COURSETYPE) AS 修课,
      RTRIM(VIEWSCHEDULE.EXAMTYPE) AS 考核,
      SCHEDULEPLAN.estimate AS 预计人数,
      isnull(attendamount.amount,0) AS 已选人数,
      RTRIM(VIEWSCHEDULE.COURSETYPENAME) AS 课程类别,
      RTRIM(VIEWSCHEDULE.SCHOOLNAME) AS 开课学院,viewschedule.dayntime 上课时间,
      scheduleplan.rem as 备注
FROM VIEWSCHEDULE LEFT OUTER JOIN
      SCHEDULEPLAN ON VIEWSCHEDULE.RECNO = SCHEDULEPLAN.RECNO left outer join
     (select count(*) as amount,courseno,[group],year,term
      from r32
      where year=:YONE and term=:TONE
      group by courseno,[group],year,term ) as attendamount on attendamount.year=scheduleplan.year and attendamount.term=scheduleplan.term and attendamount.courseno=scheduleplan.courseno and attendamount.[group]=scheduleplan.[group]
WHERE VIEWSCHEDULE.YEAR = :YTWO AND VIEWSCHEDULE.TERM =:TTWO AND
      (VIEWSCHEDULE.COURSENOGROUP LIKE '08%' ) and SCHEDULEPLAN.estimate>isnull(attendamount.amount,0)
)as b