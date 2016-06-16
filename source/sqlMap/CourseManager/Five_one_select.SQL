select * from(SELECT row_number() over(ORDER BY schools.name,R32.studentno) as row,
R32.courseno + [group] AS kh, coursename AS km, credits AS xf,
      R32.studentno AS xh, students.name AS xm, schools.name AS xsszxy
FROM R32, courses, students, classes, schools
WHERE repeat = 1 AND R32.courseno = courses.courseno AND
      R32.studentno = students.studentno AND students.classno = classes.classno AND
      classes.school = schools.school AND (R32.[group]<>'CX' and R32.[group]<>'CY' and R32.[group]<>'CZ') and
      R32.year =:year AND term =:term and left(R32.courseno,2) like :courseschool  and schools.school like :studentSCHOOL
) as b where b.row between :start and :end