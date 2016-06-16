insert into TestStudent(CourseNo,Studentno,Flag,lock,CourseNo2)
select R.COURSENO+R.[group] as kh,R.STUDENTNO,0,0,R.COURSENO+R.[group] as kh2
from R32 as R
inner join TestCourse as S on rtrim(R.COURSENO)+rtrim(R.[group])=rtrim(S.COURSENO)
where R.YEAR=:YEAR AND R.TERM=:TERM

