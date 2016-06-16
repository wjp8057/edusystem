SELECT Dbo_programs.PROGRAMNO,
Dbo_programs.PROGNAME,
CONVERT(varchar(10) , Dbo_programs.DATE, 120 ) as DATE,
Dbo_programs.REM,
Dbo_programs.URL,
Dbo_programs.VALID,
Dbo_programs.SCHOOL,
Dbo_programs.TYPE,
Dbo_schools.NAME As SchoolName,
PROGRAMTYPE.VALUE As TYPENAME,
Dbo_programs.major
FROM dbo.PROGRAMS Dbo_programs, dbo.SCHOOLS Dbo_schools,PROGRAMTYPE
WHERE   (Dbo_programs.PROGRAMNO LIKE :PROGRAMNO)
   AND  (Dbo_programs.PROGNAME LIKE :PROGRAMNAME)
   AND  (Dbo_programs.SCHOOL LIKE :SCHOOL)
   AND  (Dbo_programs.SCHOOL = Dbo_schools.SCHOOL)
   AND  (Dbo_programs.TYPE = PROGRAMTYPE.NAME)
ORDER BY PROGRAMNO

 
 
 
 
 
