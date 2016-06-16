select COUNT(A.课号) AS ROWS
from 教学质量评估成绩 AS A
where A.学年=:YEAR and A.学期=:TERM and A.课号 like :COURSENO
and A.教师 like :TEACHERNAME and A.课名 like :COURSENAME