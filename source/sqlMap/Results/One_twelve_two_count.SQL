select count(*) as ROWS from(SELECT row_number() over(ORDER BY  SUM(l_viewscore.积点分) desc) as row,students.studentno AS 学号, students.name AS 姓名,
      classes.classname AS 班级, SUM(l_viewscore.积点分) AS 积点和
FROM students, classes, l_viewscore
WHERE students.classno = :CLASSNO AND students.classno = classes.classno AND
      l_viewscore.studentno = students.studentno
GROUP BY students.studentno, students.name, classes.classname
)as b