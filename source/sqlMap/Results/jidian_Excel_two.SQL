SELECT row_number() over(ORDER BY  SUM(l_viewscore.积点分) desc) as row,students.studentno AS xh, students.name AS xm,
      classes.classname AS bj, SUM(l_viewscore.积点分) AS jdh
FROM students, classes, l_viewscore
WHERE students.classno = :CLASSNO AND students.classno = classes.classno AND
      l_viewscore.studentno = students.studentno
GROUP BY students.studentno, students.name, classes.classname
