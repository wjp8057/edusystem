select * from(SELECT row_number() over(ORDER BY scores.courseno + scores.[group],substring(classes.classname,1,2)) as row,
scores.courseno + scores.[group] kh, coursename  kcmc, COUNT(DISTINCT STUDENTNO)  hwsrdcjjls,COURSEAPPROACHES.VALUE  xkfs,
EXAMOPTIONS.VALUE khfs,classes.classname as bj
FROM SCORES inner join courseplan on courseplan.year=scores.year and courseplan.term=scores.term and courseplan.courseno=scores.courseno and courseplan.[group]=scores.[group] INNER JOIN
      COURSES ON COURSEPLAN.COURSENO = COURSES.COURSENO INNER JOIN

      EXAMOPTIONS ON COURSEPLAN.EXAMTYPE = EXAMOPTIONS.NAME INNER JOIN
      COURSEAPPROACHES ON
      COURSEPLAN.COURSETYPE = COURSEAPPROACHES.NAME inner join classes on classes.classno=courseplan.classno
WHERE scores.year =:YEAR AND scores.term =:TERM AND ((examscore IS NULL AND testscore IS NULL) OR TESTSCORE='缓考') And courseplan.SCHOOL like :SCHOOL and scores.[group] not in ('BY','ZX')
GROUP BY scores.courseno + scores.[group], coursename,COURSEAPPROACHES.VALUE,EXAMOPTIONS.VALUE,classname
)as b where b.row between :start and :end


