select count(*) as ROWS from (SELECT row_number() over(ORDER BY 学号,年,月份) as row, 年 AS 年份, 月份,  学号,姓名, classes.classname as 班级, 学院,成绩
FROM 英语CET4统考成绩 INNER JOIN STUDENTS ON STUDENTS.STUDENTNO=英语CET4统考成绩.学号
inner join classes on classes.classno=students.classno
WHERE 学号 LIKE :STUDENTNO AND STUDENTS.CLASSNO LIKE :CLASSNO
and students.SCHOOL like :school
)as b