select * from(SELECT row_number() over(ORDER BY scores.YEAR,scores.TERM,scores.COURSENO + scores.[GROUP]) as row,scores.YEAR AS xn, scores.TERM AS xq,
      scores.COURSENO + scores.[GROUP] AS kh, COURSES.COURSENAME AS km,
      COURSES.CREDITS AS xf, COURSEAPPROACHES.VALUE AS kclx,
      TESTCODE.NAME AS ksfs, case[testscore] when '' then cast(scores.EXAMSCORE as char(5)) else testscore end AS zp, case[testscore2] when '' then cast(scores.EXAMSCORE2 as char(5)) else testscore2 end AS bk,
       scores.point AS jdf,
      APPROACHCODE.NAME AS tkfs, EXAMREMOPTIONS.NAME AS ksbz
FROM (SELECT *
        FROM scores
        WHERE SCORES.STUDENTNO =:STUDENTNO) scores INNER JOIN
      COURSES ON scores.COURSENO = COURSES.COURSENO INNER JOIN
      TESTCODE ON scores.TESTTYPE = TESTCODE.CODE INNER JOIN
      APPROACHCODE ON scores.APPROACH = APPROACHCODE.CODE INNER JOIN
      EXAMREMOPTIONS ON scores.EXAMREM = EXAMREMOPTIONS.CODE INNER JOIN
      COURSEAPPROACHES ON scores.PLANTYPE = COURSEAPPROACHES.NAME inner join students on students.studentno=scores.studentno
) as b where b.row between :start and :end
