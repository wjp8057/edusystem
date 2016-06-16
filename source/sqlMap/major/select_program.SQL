select * from (SELECT row_number() over(order by Dbo_programs.PROGRAMNO)as row,Dbo_programs.PROGRAMNO,
Dbo_programs.PROGNAME,
Dbo_programs.DATE,
Dbo_programs.REM,
Dbo_programs.URL,
Dbo_programs.VALID,
Dbo_programs.SCHOOL,
Dbo_programs.TYPE,
Dbo_schools.NAME As SchoolName,
PROGRAMTYPE.VALUE As TYPENAME
FROM dbo.PROGRAMS Dbo_programs, dbo.SCHOOLS Dbo_schools,PROGRAMTYPE
WHERE   (Dbo_programs.PROGRAMNO LIKE :P_NO)
   AND  (Dbo_programs.PROGNAME LIKE :P_NAME)
   AND  (Dbo_programs.SCHOOL LIKE :SC)
   AND  (Dbo_programs.SCHOOL = Dbo_schools.SCHOOL)
   AND  (Dbo_programs.TYPE = PROGRAMTYPE.NAME)
)as b where b.row between :start and :end


