select count(*) as ROWS from(select row_number() over(order by classes.classname,学号) as row,年,月份,classes.classname as 班级,学号,姓名,成绩,等级
from 普通话等级考试成绩 inner join students on students.studentno=普通话等级考试成绩.学号
inner join classes on classes.classno=students.classno
where 学号 like :STUDENTNO AND STUDENTS.CLASSNO LIKE :CLASSNO
and students.SCHOOL like :school
) as b