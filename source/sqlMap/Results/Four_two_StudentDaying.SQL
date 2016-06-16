select * from(SELECT row_number() over(order by r32.studentno) as row,r32.studentno as  xh,students.name as xm,year,year+1 as year2 ,term
FROM r32,students
WHERE YEAR= :YEAR
AND TERM= :TERM
AND rtrim(r32.COURSENO)+(r32.[GROUP]) =:COURSENO
and r32.studentno=students.studentno
)as b where b.row between :start and :end