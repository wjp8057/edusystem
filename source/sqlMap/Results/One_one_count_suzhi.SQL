select count(*) AS ROWS from(SELECT row_number() over(ORDER BY scores.YEAR,scores.TERM,scores.COURSENO + scores.[GROUP]) as row,scores.YEAR AS xn, scores.TERM AS xq,
      scores.COURSENO + scores.[GROUP] AS kh, COURSES.COURSENAME AS km,
      COURSES.CREDITS AS xf, COURSEAPPROACHES.VALUE AS kclx,
      TESTCODE.NAME AS ksfs, case[testscore] when '' then cast(scores.EXAMSCORE as char(5)) else testscore end AS zp, case[testscore2] when '' then cast(scores.EXAMSCORE2 as char(5)) else testscore2 end AS bk,
      case[REPEAT] when 1 then '是' else '否' end AS cx, case when substring(students.classno,3,1)='4' then cast(scores.point  as char(5)) else '-' end AS jdf,
      APPROACHCODE.NAME AS tkfs, EXAMREMOPTIONS.NAME AS ksbz
FROM (SELECT *
        FROM scores
        WHERE SCORES.STUDENTNO =:STUDENTNO) scores INNER JOIN
      COURSES ON scores.COURSENO = COURSES.COURSENO INNER JOIN
      TESTCODE ON scores.TESTTYPE = TESTCODE.CODE INNER JOIN
      APPROACHCODE ON scores.APPROACH = APPROACHCODE.CODE INNER JOIN
      EXAMREMOPTIONS ON scores.EXAMREM = EXAMREMOPTIONS.CODE INNER JOIN
      COURSEAPPROACHES ON scores.PLANTYPE = COURSEAPPROACHES.NAME inner join students on students.studentno=scores.studentno
where (scores.courseno like '08%' or scores.courseno like '007%' or scores.[group] like 'G_') and 
(testscore in ('合格','及格','中等','良好','优秀') or examscore >=60)
) as b