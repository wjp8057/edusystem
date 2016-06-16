select * from(select row_number() over(order by classes.classname,学号,年,月份) as row,年 as n,月份 as yf,classes.classname as bj,学号 as xh,姓名 as xm,成绩 as cj,等级 as dj
from 计算机统考成绩 inner join students on students.studentno=计算机统考成绩.学号
inner join classes on classes.classno=students.classno
where 学号 like :STUDENTNO and students.classno like :CLASSNO
) as b