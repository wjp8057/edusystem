select * from(SELECT row_number() over(order by dbo.getOne(RTRIM(COURSENOGROUP))) as row,RTRIM(COURSENOGROUP) AS kh,
    dbo.getOne(RTRIM(COURSENAME)) AS km,
    dbo.getOne(CREDITS) AS xf,
    dbo.getOne(WEEKHOURS) AS zxs,
    dbo.getOne(WEEKEXPEHOURS) AS zsy,
    dbo.getOne(RTRIM(COURSETYPE)) AS xk,
    dbo.getOne(RTRIM(EXAMTYPE)) AS kh2,
    dbo.getOne(RTRIM(SCHOOLNAME)) AS kkxy,
    dbo.getOne(RTRIM(CLASSNONAME)) AS bj,
    dbo.GROUP_CONCAT_MERGE(RTRIM(TEACHERNONAME),',') AS js,
    dbo.getOne(RTRIM(REM)) AS bz,
    dbo.GROUP_CONCAT_MERGE(RTRIM(DAYNTIME),',') AS kcap
FROM VIEWSCHEDULE
WHERE
   YEAR = :YEAR
   AND TERM = :TERM
   AND (TEACHERNO=:TEACHERNO)
group by COURSENOGROUP
) as b where b.row between :start and :end