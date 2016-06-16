select * from(SELECT row_number() over(order by scores.courseno,schools.name,classes.classname,SCORES.STUDENTNO)as row,SCORES.COURSENO+scores.[group] AS kh, COURSES.COURSENAME AS kcmc,
      SCORES.STUDENTNO AS xh, STUDENTS.NAME AS xm,classes.classname bj, schools.name as xy,
APPROACHCODE.name AS xkfs,
case[testscore] when '' then cast(scores.EXAMSCORE as char(5)) else testscore end AS zp,
case[testscore2] when '' then cast(scores.EXAMSCORE2 as char(5)) else testscore2 end AS bk,
EXAMREMOPTIONS.NAME AS bz
FROM SCORES INNER JOIN
      STUDENTS ON SCORES.STUDENTNO = STUDENTS.STUDENTNO INNER JOIN
      COURSES ON SCORES.COURSENO = COURSES.COURSENO INNER JOIN
      EXAMREMOPTIONS ON EXAMREMOPTIONS.CODE=SCORES.EXAMREM  INNER JOIN
      APPROACHCODE ON scores.APPROACH = APPROACHCODE.CODE INNER JOIN
      TESTCODE ON scores.TESTTYPE = TESTCODE.CODE
inner join classes on classes.classno=students.classno
inner join schools on schools.school=classes.school
WHERE (SCORES.[YEAR] =:YEAR) AND (SCORES.TERM = :TERM) and
    ( scores.testscore not in ('合格','及格','良好','中等','优秀') or scores.testscore is null ) and (scores.examscore<60 or scores.examscore is null)
    and (scores.testscore2 not in ('合格','及格','良好','中等','优秀')or  scores.testscore2 is null) and ( scores.examscore2<60 or scores.examscore2 is null)
and scores.courseno+scores.[group] like :COURSENO
)as b where b.row between :start and :end