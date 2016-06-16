select count(*) as ROWS from(SELECT row_number() over(ORDER BY scores.courseno + scores.[group],substring(classes.classname,1,2)) as b,scores.courseno + scores.[group] 课号, coursename  课程名称, COUNT(DISTINCT STUDENTNO)  还未输入成绩的记录数,COURSEAPPROACHES.VALUE  修课方式,EXAMOPTIONS.VALUE 考核方式,classes.classname as 班级
FROM SCORES inner join courseplan on courseplan.year=scores.year and courseplan.term=scores.term and courseplan.courseno=scores.courseno and courseplan.[group]=scores.[group] INNER JOIN
      COURSES ON COURSEPLAN.COURSENO = COURSES.COURSENO INNER JOIN
      EXAMOPTIONS ON COURSEPLAN.EXAMTYPE = EXAMOPTIONS.NAME INNER JOIN
      COURSEAPPROACHES ON
      COURSEPLAN.COURSETYPE = COURSEAPPROACHES.NAME inner join classes on classes.classno=courseplan.classno
WHERE scores.year =:YEAR AND scores.term =:TERM AND ((examscore IS NULL AND testscore IS NULL) OR TESTSCORE='缓考') And courseplan.SCHOOL like :SCHOOL and scores.[group] not in ('BY','ZX')
GROUP BY scores.courseno + scores.[group], coursename,COURSEAPPROACHES.VALUE,EXAMOPTIONS.VALUE,classname
)as b