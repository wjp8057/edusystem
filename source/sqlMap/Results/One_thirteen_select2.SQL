select * from(select row_number() over(order by 学年,classes.classname,学号) as row,学年 as xn,学号 as xh,students.name as xm,成绩 as dj,分数 as fs,classes.classname as bj
from 体质健康测试成绩 inner join students  on students.studentno=体质健康测试成绩.学号
inner join classes on classes.classno=students.classno
where 学年 like 0 and students.classno like :CLASSNO and students.studentno like :STUDENTNO
)as b where b.row between :start and :end