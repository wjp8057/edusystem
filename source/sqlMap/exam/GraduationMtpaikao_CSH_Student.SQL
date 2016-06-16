insert into TestStudent(CourseNo,Studentno,Flag,Lock,CourseNo2)
select S.COURSENO,S.STUDENTNO,0,0,T.Courseno2
from SCORES as S inner join COURSES AS C ON S.COURSENO=C.COURSENO
inner join TESTCOURSE T ON S.COURSENO=T.COURSENO  WHERE S.YEAR=:YEAR AND S.TERM=:TERM AND S.approach='D'