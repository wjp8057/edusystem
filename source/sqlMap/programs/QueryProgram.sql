SELECT Dbo_programs.PROGRAMNO,
Dbo_programs.PROGNAME,
Dbo_programs.DATE,
Dbo_programs.REM,
Dbo_programs.URL,
Dbo_programs.VALID,
Dbo_programs.SCHOOL,
Dbo_programs.TYPE,
Dbo_programs.MAJOR,
Dbo_schools.NAME As SchoolName,
PROGRAMTYPE.VALUE As TYPENAME,lock
FROM dbo.PROGRAMS Dbo_programs, dbo.SCHOOLS Dbo_schools,PROGRAMTYPE
WHERE   (Dbo_programs.PROGRAMNO = :programno)
AND  (Dbo_programs.SCHOOL = Dbo_schools.SCHOOL)
AND  (Dbo_programs.TYPE = PROGRAMTYPE.NAME)


 
 
 
 
 
