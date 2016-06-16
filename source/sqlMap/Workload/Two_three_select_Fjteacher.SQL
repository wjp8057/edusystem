select TEACHERS.TEACHERNO as jsh,
TEACHERS.NAME as jsxm,
SCHOOLS.NAME as jsxy,
SCHOOLS.SCHOOL as SCHOOL,
POSITIONS.VALUE as zc,
TEACHERTYPE.VALUE as gwlb,
isnull([work].[work],0) as grgzl
from work inner join TEACHERS
on work.TeacherNo=TEACHERS.teacherno
LEFT JOIN POSITIONS ON POSITIONS.NAME=TEACHERS.POSITION
INNER JOIN TEACHERTYPE ON TEACHERS.TYPE=TEACHERTYPE.NAME
INNER JOIN SCHOOLS ON TEACHERS.SCHOOL=SCHOOLS.SCHOOL
where work.Totalwork_id in(
select Totalwork_id from totalwork where RTRIM(courseno)+RTRIM([group])=:courseno
)
/*
 FROM TEACHERS INNER JOIN SCHOOLS ON TEACHERS.SCHOOL=SCHOOLS.SCHOOL
INNER JOIN POSITIONS ON POSITIONS.NAME=TEACHERS.POSITION
INNER JOIN TEACHERTYPE ON TEACHERS.TYPE=TEACHERTYPE.NAME
left join work on (work.teacherno=teachers.teacherno and work.totalwork_id in (select totalwork_id from totalwork where year=:yone
and term=:tone and RTRIM(courseno)+RTRIM([group])=:courseno1))
WHERE TEACHERS.TEACHERNO in (select teacherno from totalwork where totalwork_id in (select totalwork_id from totalwork where year=:ytwo
and term=:ttwo and RTRIM(courseno)+RTRIM([group])=:courseno2)
)*/