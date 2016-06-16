select courses.CourseNo,
courses.CourseName,
courses.Credits,
courses.School,
Schools.Name As SchoolName,
COURSETYPEOPTIONS.VALUE AS COURSETYPE
from courses
JOIN Schools ON COURSES.SCHOOL=SCHOOLS.SCHOOL
JOIN COURSETYPEOPTIONS ON COURSES.TYPE=COURSETYPEOPTIONS.NAME
where
Courses.CourseNo like :COURSENO
and Courses.CourseName like :COURSENAME
and Courses.School like :SCHOOL
AND COURSETYPEOPTIONS.NAME LIKE :COURSETYPE

 
