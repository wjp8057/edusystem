SELECT scores.YEAR AS YEAR, scores.TERM AS TERM,
      scores.COURSENO + scores.[GROUP] AS COURSENO, COURSES.COURSENAME AS COURSENAME,
      COURSES.CREDITS AS CREDITS, COURSEAPPROACHES.VALUE AS APPROACHES,TESTCODE.NAME AS EXAMTYPE,
      case[testscore] when '' then cast(scores.EXAMSCORE as char(5)) else testscore end AS EXAMSCORE,
      case[testscore2] when '' then cast(scores.EXAMSCORE2 as char(5)) else testscore2 end AS EXAMSCORE2,
      case when substring(students.classno,3,1)='4' then cast(scores.point  as char(5)) else '-' end AS POINT,
      APPROACHCODE.NAME AS APPROACHNAME, EXAMREMOPTIONS.NAME AS REM
FROM (SELECT *
        FROM scores
        WHERE SCORES.STUDENTNO =:STUDENTNO) scores INNER JOIN
      COURSES ON scores.COURSENO = COURSES.COURSENO INNER JOIN
      TESTCODE ON scores.TESTTYPE = TESTCODE.CODE INNER JOIN
      APPROACHCODE ON scores.APPROACH = APPROACHCODE.CODE INNER JOIN
      EXAMREMOPTIONS ON scores.EXAMREM = EXAMREMOPTIONS.CODE INNER JOIN
      COURSEAPPROACHES ON scores.PLANTYPE = COURSEAPPROACHES.NAME inner join students on students.studentno=scores.studentno
ORDER BY YEAR, TERM, COURSENO