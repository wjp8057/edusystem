SELECT count(*) 
FROM R32, courses, approachcode, testcode, courseapproaches, students, schools as schools1,schools as schools2,classes 
WHERE (approach = 'B' OR
      approach = 'C') AND R32.courseno = courses.courseno AND students.classno = classes.classno AND classes.school = schools2.school AND
      R32.approach = approachcode.code AND R32.examtype = testcode.code AND 
      R32.coursetype = courseapproaches.name AND R32.studentno = students.studentno AND 
      courses.school = schools1.school AND R32.year =:year AND R32.term =:term
      and schools1.school like :cSchool and schools2.school like :school