SELECT Dbo_courseplan.YEAR,
    Dbo_courseplan.TERM,
    Dbo_courseplan.COURSENO,
    Dbo_courseplan.[GROUP],
    Dbo_courses.COURSENAME,
    Dbo_classes.CLASSNO,
    Dbo_classes.CLASSNAME,
    Dbo_courses.CREDITS,
    Dbo_courses.HOURS,
    Dbo_courseplan.WEEKS,
    Dbo_courseapproaches.VALUE AS COURSETYPE,
    Dbo_courseplan.COURSETYPE AS TYPE,
    Dbo_examoptions.VALUE AS EXAMTYPE,
    Dbo_courseplan.EXAMTYPE AS EXAM,
    Dbo_courseplan.ATTENDENTS,
    Dbo_schools.NAME AS SCHOOLNAME,
    Dbo_courseplan.SCHOOL AS SCHOOL,
    Dbo_courseplan.REM AS REM,
    Dbo_classes.SCHOOL AS CLASSSCHOOL
FROM dbo.COURSEPLAN Dbo_courseplan,
    dbo.COURSEAPPROACHES Dbo_courseapproaches,
    dbo.EXAMOPTIONS Dbo_examoptions,
    dbo.SCHOOLS Dbo_schools,
    dbo.COURSES Dbo_courses,
    dbo.CLASSES Dbo_classes
WHERE  (Dbo_courseplan.COURSENO = Dbo_courses.COURSENO)
   AND  (Dbo_courseplan.SCHOOL = Dbo_schools.SCHOOL)
   AND  (Dbo_courseplan.COURSETYPE = Dbo_courseapproaches.NAME)
   AND  (Dbo_courseplan.EXAMTYPE = Dbo_examoptions.NAME)
   AND (Dbo_courseplan.CLASSNO =Dbo_classes.CLASSNO)
   AND (Dbo_courseplan.YEAR=:YEAR)
   AND (Dbo_courseplan.TERM=:TERM)
   AND (Dbo_courseplan.COURSENO LIKE :COURSENO)
   AND (Dbo_courseplan.[GROUP] LIKE :GROUP)
   AND (Dbo_courseplan.SCHOOL LIKE :SCHOOL)
   AND (Dbo_courseplan.COURSETYPE LIKE :COURSETYPE)
   AND (Dbo_courseplan.CLASSNO LIKE :CLASSNO)
   AND (Dbo_courseplan.EXAMTYPE LIKE :EXAMTYPE)
