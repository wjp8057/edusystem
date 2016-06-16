select count(*) as ROWS from (select 年,月份,学号,姓名,CLASSES.CLASSNAME AS 班级,学院,成绩 from
英语B级统考成绩 inner join students on students.studentno=英语B级统考成绩.学号
INNER JOIN CLASSES ON CLASSES.CLASSNO=STUDENTS.CLASSNO
where students.studentno like :STUDENTNO AND STUDENTS.CLASSNO LIKE :CLASSNO
and students.SCHOOL like :school
)as b