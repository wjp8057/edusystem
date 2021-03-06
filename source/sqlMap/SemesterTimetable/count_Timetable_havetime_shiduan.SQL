select count (*) as ROWS from (SELECT row_number() over(order by dbo.GETONE(DAYNTIME),dbo.GETONE(COURSENOGROUP))as row,dbo.GETONE(RTRIM(COURSENOGROUP)) AS kh,
    dbo.GETONE(RTRIM(COURSENAME)) AS km,
    dbo.GETONE(CREDITS) AS xf,
    dbo.GETONE(WEEKHOURS) AS zxs,
    dbo.GETONE(WEEKEXPEHOURS) AS zsy,
    dbo.GETONE(RTRIM(COURSETYPE)) AS xk,
    dbo.GETONE(RTRIM(EXAMTYPE)) AS kh2,
    dbo.GETONE(RTRIM(SCHOOLNAME)) AS kkxy,
    dbo.GROUP_CONCAT_MERGE(RTRIM(CLASSNONAME),'; ') AS bj,
    dbo.GROUP_CONCAT_MERGE(RTRIM(TEACHERNONAME),'; ') AS js,
    dbo.GETONE(RTRIM(REM)) AS bz,
    dbo.GROUP_CONCAT_MERGE(RTRIM(DAYNTIME),'; ') AS kcap
FROM VIEWSCHEDULE
WHERE
   YEAR = :YEAR
   AND TERM = :TERM
   AND COURSENO LIKE :COURSENO
   AND [GROUP] LIKE :GROUP
   AND SCHOOL LIKE :SCHOOL
   AND TYPE LIKE :COURSETYPE
   AND CLASSNO LIKE :CLASSNO
   AND CLASSNAME LIKE :CLASSNAME
   AND APPROACH LIKE :APPROACHES
   AND EXAM LIKE :EXAMTYPE
   AND (TEACHERNAME LIKE :TEACHERNAME  OR TEACHERNAME IS NULL)
   AND (TEACHERNO LIKE :TEACHERNO  OR TEACHERNO IS NULL)
   AND (DAY LIKE :DAY)
   AND (TIMEBITS & :TIME <> 0)
   group by COURSENOGROUP
)as b

