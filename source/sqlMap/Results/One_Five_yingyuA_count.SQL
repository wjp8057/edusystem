select count(*) as ROWS from (select row_number() over(order by 学号) as row,年 as n,月份 as yf,学号 as xh,姓名 as xm,CLASSES.CLASSNAME AS bj,学院 as xy,成绩 as cj from
英语A级统考成绩 inner join students on students.studentno=英语A级统考成绩.学号
INNER JOIN CLASSES ON CLASSES.CLASSNO=STUDENTS.CLASSNO
where students.studentno like :STUDENTNO AND STUDENTS.CLASSNO LIKE :CLASSNO
and students.SCHOOL like :school
)as b