select COUNT(*) AS ROWS
from 学绩分,students
where 学绩分.学号=students.studentno and students.classno=:CLASSNO
and 学绩分.学年=:YEAR and 学绩分.学期=:TERM
