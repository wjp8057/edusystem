select count(*) as ROWS from(SELECT row_number() over(ORDER BY  schools1.name,approachcode.name,R32.courseno+[group],R32.studentno) as row,
      schools2.name AS xsszxy,R32.studentno xh, students.name xm, R32.courseno+[group] kh,
      courses.coursename kcmc, schools1.name kkxy,
      courseapproaches.value kclx, credits xf, testcode.name khfs,
      approachcode.name xkfs
FROM R32, courses, approachcode, testcode, courseapproaches, students, schools as schools1,schools as schools2,classes

WHERE (approach = 'B' OR
      approach = 'C') AND R32.courseno = courses.courseno AND students.classno = classes.classno AND classes.school = schools2.school AND
      R32.approach = approachcode.code AND R32.examtype = testcode.code AND
      R32.coursetype = courseapproaches.name AND R32.studentno = students.studentno AND
      courses.school = schools1.school AND R32.year =:year AND R32.term =:term
      and schools1.school like :courseschool and schools2.school like :studentschool
) as b