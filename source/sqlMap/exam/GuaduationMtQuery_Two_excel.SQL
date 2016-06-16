select * from(SELECT  row_number() over(order by STUDENTSCHOOL.NAME,scores.studentno,SCORES.COURSENO + SCORES.[GROUP]) as row,
      scores.studentno xh,STUDENTS.NAME xm, STUDENTSCHOOL.NAME szxy, classes.classname szbj,
      SCORES.COURSENO + SCORES.[GROUP] kh,COURSES.COURSENAME kcmc, COURSES.CREDITS xf, COURSESCHOOL.NAME kkxy,courseapproaches.value xklx,
      SCORES.EXAMSCORE ksfs, SCORES.TESTSCORE kcfs,
      APPROACHCODE.NAME xkfs
FROM SCORES inner join  STUDENTS ON SCORES.STUDENTNO = STUDENTS.STUDENTNO inner join
      CLASSES ON STUDENTS.CLASSNO = CLASSES.CLASSNO inner  join
      SCHOOLS STUDENTSCHOOL ON students.SCHOOL = STUDENTSCHOOL.SCHOOL inner join
      APPROACHCODE ON SCORES.APPROACH = APPROACHCODE.CODE INNER JOIN
      COURSES ON SCORES.COURSENO = COURSES.COURSENO INNER JOIN
      SCHOOLS COURSESCHOOL ON  COURSES.SCHOOL =COURSESCHOOL.SCHOOL
INNER JOIN  courseapproaches ON scores.plantype=courseapproaches.name
WHERE scores.year =:YEAR AND scores.term =:TERM and approach='D' and
STUDENTSCHOOL.school like :SSCHOOLNO and COURSESCHOOL.school like :CSCHOOLNO  and
scores.studentno like :STUDENTNO and scores.courseno like :COURSENO
) as b
