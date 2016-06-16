SELECT Dbo_courses.COURSENO as courseno,
Dbo_courses.COURSENAME as coursename,
Dbo_courses.CREDITS as credit,
Dbo_schools.NAME as kkdw
FROM dbo.R29 Dbo_r29, dbo.COURSES Dbo_courses, dbo.SCHOOLS Dbo_schools
WHERE   (Dbo_r29.COURSENO = :COURSENO)
   AND  (Dbo_r29.REQNO = Dbo_courses.COURSENO)
   AND  (Dbo_courses.SCHOOL = Dbo_schools.SCHOOL)