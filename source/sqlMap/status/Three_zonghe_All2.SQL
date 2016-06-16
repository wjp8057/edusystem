SELECT COUNT(Dbo_students.STUDENTNO)AS NUMBER
FROM dbo.STUDENTS Dbo_students, dbo.SCHOOLS Dbo_schools, dbo.CLASSES Dbo_classes, dbo.REGDATA Dbo_regdata, dbo.REGCODEOPTIONS Dbo_regcodeoptions, dbo.STATUSOPTIONS Dbo_statusoptions
WHERE  (Dbo_students.STUDENTNO = Dbo_regdata.STUDENTNO)
   AND  (Dbo_regdata.REGCODE *= Dbo_regcodeoptions.NAME)
   AND  (Dbo_students.STATUS *= Dbo_statusoptions.NAME)
   AND  (Dbo_students.CLASSNO *= Dbo_classes.CLASSNO)
   AND  (Dbo_students.SCHOOL *= Dbo_schools.SCHOOL)
   AND  ( (Dbo_classes.CLASSNO LIKE :CLASSNO)
   AND  (Dbo_students.SCHOOL LIKE :SCHOOL)
   AND  (Dbo_students.STATUS LIKE :STATUS)
   AND  (Dbo_regdata.YEAR = :YEAR)
   AND  (Dbo_regdata.TERM = :TERM) )