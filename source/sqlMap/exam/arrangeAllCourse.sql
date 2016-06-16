SELECT substring(TestCourse.Courseno2,1,7) AS courseno,rtrim(courses.coursename) as coursename, COUNT(*) AS amount,0 as flag FROM TestStudent
INNER JOIN COURSES ON COURSES.COURSENO = SUBSTRING(TestStudent.CourseNo, 1, 7)
inner join testcourse on testcourse.courseno=teststudent.courseno
where testcourse.flag=0 and testcourse.lock=0
GROUP BY substring(TestCourse.Courseno2,1,7),courses.coursename ORDER BY amount  desc