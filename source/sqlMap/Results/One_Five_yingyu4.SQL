select * from (SELECT row_number() over(ORDER BY 学号,年,月份) as row, 年 AS n, 月份 as yf,学号 as xh,姓名 as xm,classes.classname as bj, 学院 as xy,成绩 as cj
FROM 英语CET4统考成绩 INNER JOIN STUDENTS ON STUDENTS.STUDENTNO=英语CET4统考成绩.学号
inner join classes on classes.classno=students.classno
WHERE 学号 LIKE :STUDENTNO AND STUDENTS.CLASSNO LIKE :CLASSNO
and students.SCHOOL like :school
)as b where b.row between :start and :end