SELECT Dbo_courses.COURSENO as courseno,
Dbo_courses.COURSENAME as coursename,
Dbo_courses.CREDITS as credit,
Dbo_schools.NAME as kkdw
FROM dbo.R33 Dbo_r33, dbo.COURSES Dbo_courses, dbo.SCHOOLS Dbo_schools
WHERE   (Dbo_r33.COURSENO = :COURSENO)
   AND  (Dbo_r33.EQNO = Dbo_courses.COURSENO)
   AND  (Dbo_courses.SCHOOL = Dbo_schools.SCHOOL)