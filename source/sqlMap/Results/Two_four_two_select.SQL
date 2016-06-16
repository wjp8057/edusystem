select * from(SELECT row_number() over(ORDER BY [L_view_不及格的必修课].学号) as row,[L_view_不及格的必修课].学号 as xh, [L_view_不及格的必修课].姓名 as xm, COUNT(*)
      AS  mc
FROM [L_view_不及格的必修课], students, classes
WHERE 学号 LIKE :STUDENTNO AND students.studentno = [L_view_不及格的必修课].学号 AND
      students.classno = classes.classno AND classes.school LIKE :SCHOOL AND
      (students.studentno LIKE '00%' OR
      students.studentno LIKE '013%' OR
      students.studentno LIKE '9%' OR
      students.studentno LIKE '022%') AND (status <> 'J' AND status <> 'E') and 课号 not like '040t1%'
GROUP BY [L_view_不及格的必修课].学号, [L_view_不及格的必修课].姓名
) as b where b.row between :start and :end