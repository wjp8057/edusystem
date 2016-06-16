insert into TestStudent(CourseNo,Studentno,Flag,Lock,CourseNo2,status)
select distinct rtrim(scores.courseno)+rtrim(scores.[group]),
scores.studentno,0,0,rtrim(scores.courseno)+rtrim(scores.[group]),0
from scores inner join courseplan on courseplan.courseno=scores.courseno and courseplan.[group]=scores.[group]
and scores.year=courseplan.year and scores.term=courseplan.term
inner join scheduleplan on (
rtrim(scheduleplan.courseno)+rtrim(scheduleplan.[group])=rtrim(scores.courseno)+rtrim(scores.[group])
and scheduleplan.year=scores.year and scheduleplan.term=scores.term)
inner join courses on courses.courseno=scores.courseno
inner join students on students.studentno=scores.studentno
inner join schools on schools.school=courses.school
inner join classes on classes.classno=students.classno
inner join schools  as sc on sc.school=classes.school
where (scores.[YEAR] = :year) AND (scores.TERM = 1) AND scheduleplan.degree=1 AND (EXAMSCORE < 75)