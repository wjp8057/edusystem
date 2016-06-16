select count(*) as ROWS from(select row_number() over(order by 学年,classes.classname,学号) as row,学年,学号,students.name as 姓名,成绩 as 等级,分数,classes.classname as 班级
from 体质健康测试成绩 inner join students  on students.studentno=体质健康测试成绩.学号
inner join classes on classes.classno=students.classno
where 学年 like :YEAR and students.classno like :CLASSNO and students.studentno like :STUDENTNO
)as b