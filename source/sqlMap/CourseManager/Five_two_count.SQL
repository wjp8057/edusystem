select count(*) AS ROWS from (select row_number() over(ORDER BY b.开课学院, b.课号, b.学生所在学院, b.学号) as row,b.* from(SELECT distinct schools1.name AS 开课学院, R32.courseno + R32.[group] AS 课号,
      coursename AS 课名, credits AS 学分, R32.studentno AS 学号, students.name AS 姓名,
      schools2.name AS 学生所在学院
FROM R32, courses, students, schools AS schools1, schools AS schools2, classes,
      courseplan
WHERE repeat = 1 AND R32.courseno = courses.courseno AND
      R32.studentno = students.studentno AND courses.school = schools1.school AND
      students.classno = classes.classno AND classes.school = schools2.school AND
      R32.year =:year AND R32.term =:term AND
      courseplan.courseno + courseplan.[group] = R32.courseno + R32.[group] AND
      courseplan.classno = '222222' and schools1.school like :courseschool and schools2.school like :studentschool
) as b)as c