SELECT 年 AS Y, 月份 as M,  学号 as SNO, 姓名 as N,classes.classname as CLASSNAME, 学院 as SCHOOL,成绩 as R
FROM 英语CET4统考成绩 INNER JOIN STUDENTS ON STUDENTS.STUDENTNO=英语CET4统考成绩.学号
inner join classes on classes.classno=students.classno
WHERE 学号 = :STUDENTNO AND STUDENTS.CLASSNO = :CLASSNO
ORDER BY SNO,Y, M