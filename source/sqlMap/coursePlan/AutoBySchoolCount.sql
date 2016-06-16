SELECT count(*) as NCOUNT
FROM dbo.PROGRAMS Dbo_Programs ,dbo.R16 Dbo_r16, dbo.R12 Dbo_r12, dbo.CLASSES Dbo_classes, dbo.COURSES Dbo_courses
WHERE Dbo_r16.PROGRAMNO = Dbo_r12.PROGRAMNO
AND Dbo_Programs.ProgramNo=Dbo_r12.ProgramNo
AND Dbo_r12.COURSENO = Dbo_courses.COURSENO
AND Dbo_r16.CLASSNO = Dbo_classes.CLASSNO
AND Dbo_r12.YEAR = Dbo_Classes.Grade and Dbo_r12.TERM=:TERM