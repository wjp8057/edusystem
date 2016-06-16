SELECT COUNT(dbo_COURSEPLAN.YEAR )AS Rows
FROM dbo.COURSEPLAN Dbo_courseplan,
    dbo.COURSEAPPROACHES Dbo_courseapproaches,
    dbo.EXAMOPTIONS Dbo_examoptions,
    dbo.SCHOOLS Dbo_schools,
    dbo.COURSES Dbo_courses,
    dbo.CLASSES Dbo_classes
WHERE (Dbo_courseplan.COURSENO = Dbo_courses.COURSENO)
   AND (Dbo_courseplan.SCHOOL = Dbo_schools.SCHOOL)
   AND (Dbo_courseplan.COURSETYPE = Dbo_courseapproaches.NAME)
   AND (Dbo_courseplan.EXAMTYPE = Dbo_examoptions.NAME)
   AND (Dbo_courseplan.CLASSNO =Dbo_classes.CLASSNO)
   AND (Dbo_courseplan.YEAR=:YEAR)
   AND (Dbo_courseplan.TERM=:TERM)
   AND (Dbo_courseplan.COURSENO LIKE :COURSENO)
   AND (Dbo_courseplan.[GROUP] LIKE :GROUP)
   AND (Dbo_courseplan.SCHOOL LIKE :SCHOOL)
   AND (Dbo_courseplan.COURSETYPE LIKE :COURSETYPE)
   AND (Dbo_courseplan.CLASSNO LIKE :CLASSNO)
   AND (Dbo_courseplan.EXAMTYPE LIKE :EXAMTYPE)