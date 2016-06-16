select * from(select row_number() over(order by classes.classname,学号) as row,年 as n,月份 as yf,classes.classname as bj,学号 as xh,姓名 as xm,成绩 as cj,等级 as dj
from 普通话等级考试成绩 inner join students on students.studentno=普通话等级考试成绩.学号
inner join classes on classes.classno=students.classno
where 学号 like :STUDENTNO AND STUDENTS.CLASSNO LIKE :CLASSNO
and students.SCHOOL like :school
) as b where b.row between :start and :end