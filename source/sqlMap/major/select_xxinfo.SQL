SELECT Dbo_courses.COURSENO,
Dbo_courses.COURSENAME,
Dbo_courses.CREDITS,
Dbo_courses.HOURS,
Dbo_courses.EXPERIMENTS,
Dbo_courses.COMPUTING,
Dbo_courses.KHOURS,
Dbo_courses.ZHOURS,
Dbo_courses.SHOURS,
Dbo_coursetypeoptions.VALUE as TYPE,
Dbo_courses.SYLLABUS,
REPLACE(Dbo_courses.OLDCOURSENO,'&',';') as OBSELETE,
Dbo_courses.REM as REM,
Dbo_schools.NAME as SCHOOL,
Dbo_schools.SCHOOL AS SCHOOLNO
FROM dbo.COURSES Dbo_courses, dbo.SCHOOLS Dbo_schools, dbo.COURSETYPEOPTIONS Dbo_coursetypeoptions
WHERE   (Dbo_courses.SCHOOL = Dbo_schools.SCHOOL)
   AND  (Dbo_courses.TYPE = Dbo_coursetypeoptions.NAME)
   AND  (Dbo_courses.COURSENO = :COURSENO)