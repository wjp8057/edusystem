select * from (select row_number() over(order by 学绩分 desc) as row,学号 as xh,students.name as xm,学绩分 as xjf
from 学绩分,students
where 学绩分.学号=students.studentno and students.classno=:CLASSNO
and 学绩分.学年=:YEAR and 学绩分.学期=:TERM
)as b where b.row between :start and :end