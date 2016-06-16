select count(*) as ROWS from(SELECT row_number() over(order by 课号) as row,课号 AS kh, 课程名称 AS kcmc, 修课方式 AS xkfs,
      考核方式 AS khfs,
      CASE [状态] WHEN 0 THEN '开放' ELSE '锁定' END AS 状态,'' AS SL
FROM (SELECT DISTINCT
              COURSEPLAN.COURSENO + COURSEPLAN.[GROUP] AS 课号,
              COURSES.COURSENAME AS 课程名称,
              COURSEAPPROACHES.[VALUE] AS 修课方式,
              EXAMOPTIONS.[VALUE] AS 考核方式, SUM(lock) AS 状态
        FROM COURSEPLAN INNER JOIN
              COURSEAPPROACHES ON
              COURSEPLAN.COURSETYPE = COURSEAPPROACHES.NAME INNER JOIN
              COURSES ON
              COURSES.COURSENO = COURSEPLAN.COURSENO INNER JOIN
              EXAMOPTIONS ON
              EXAMOPTIONS.NAME = COURSEPLAN.EXAMTYPE INNER JOIN
              SCORES ON SCORES.[YEAR] = COURSEPLAN.[YEAR] AND
              SCORES.TERM = COURSEPLAN.TERM AND
              COURSEPLAN.COURSENO = SCORES.COURSENO AND
              SCORES.[GROUP] = COURSEPLAN.[GROUP]
        where scores.year=:YEAR and scores.term=:TERM and courseplan.school=:SCHOOL
and scores.[group] not in ('BY','ZX')
        GROUP BY COURSEPLAN.COURSENO + COURSEPLAN.[GROUP],
              COURSES.COURSENAME, COURSEAPPROACHES.[VALUE],
              EXAMOPTIONS.[VALUE]) AS TEMP
) as b