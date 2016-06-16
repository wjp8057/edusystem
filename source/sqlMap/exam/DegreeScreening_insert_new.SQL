insert into TestStudent(CourseNo,Studentno,Flag,Lock,CourseNo2)
select distinct rtrim(scores.courseno)+rtrim(scores.[group]),
scores.studentno,0,0,rtrim(scores.courseno)+rtrim(scores.[group])
from scores inner join courseplan on courseplan.courseno=scores.courseno and courseplan.[group]=scores.[group]
and scores.year=courseplan.year and scores.term=courseplan.term
inner join courses on courses.courseno=scores.courseno
inner join students on students.studentno=scores.studentno
inner join schools on schools.school=courses.school
inner join classes on classes.classno=students.classno
inner join schools  as sc on sc.school=classes.school
where scores.examscore<60 and scores.testscore not in ('合格','及格','中等','良好','优秀') and qm not in ('缺考','违纪')
and scores.year=:year and scores.term=:term and courseplan.coursetype!='E'