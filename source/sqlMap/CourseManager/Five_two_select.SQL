select * from (select row_number() over(ORDER BY b.kkxy, b.kh, b.xsszxy, b.kh) as row,b.* from
(SELECT distinct schools1.name AS kkxy, R32.courseno + R32.[group] AS kh,
      coursename AS km, credits AS xf, R32.studentno AS xh, students.name AS xm,
      schools2.name AS xsszxy
FROM R32, courses, students, schools AS schools1, schools AS schools2, classes,
      courseplan
WHERE repeat = 1 AND R32.courseno = courses.courseno AND
      R32.studentno = students.studentno AND courses.school = schools1.school AND
      students.classno = classes.classno AND classes.school = schools2.school AND
      R32.year =:year AND R32.term =:term AND
      courseplan.courseno + courseplan.[group] = R32.courseno + R32.[group] AND
      courseplan.classno = '222222' and schools1.school like :courseschool and schools2.school like :studentschool
) as b)as c where c.row between :start and :end