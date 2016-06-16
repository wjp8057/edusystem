select count(*) as ROWS from(SELECT row_number() over(order by r32.studentno) as row,r32.studentno as  学号,students.name as 姓名,year,year+1 as year2 ,term
FROM r32,students
WHERE YEAR= :YEAR
AND TERM= :TERM
AND rtrim(r32.COURSENO)+(r32.[GROUP]) =:COURSENO
and r32.studentno=students.studentno
)as b