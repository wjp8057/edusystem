delete from TestCourse;
insert into TestCourse(CourseNo,CourseName,Flag,Lock,classes,CourseNo2)
select S.COURSENO,C.COURSENAME,0,0,NULL,S.COURSENO
from SCORES as S inner join COURSES AS C ON S.COURSENO=C.COURSENO WHERE S.YEAR=:YEAR AND S.TERM=:TERM AND S.approach='D'
group by S.COURSENO,C.COURSENAME