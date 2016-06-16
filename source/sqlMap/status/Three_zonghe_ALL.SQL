SELECT Count(Dbo_students.STUDENTNO)AS NUMBER
FROM dbo.STUDENTS Dbo_students
WHERE   (Dbo_students.SCHOOL LIKE :SCHOOL)
   AND  (Dbo_students.CLASSNO LIKE :CLASSNO)
   AND  (Dbo_students.STATUS LIKE :STATUS)