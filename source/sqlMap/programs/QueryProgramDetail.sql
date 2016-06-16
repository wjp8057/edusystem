select R12.CourseNo As CourseNo,
Courses.CourseName As CourseName ,
Courses.Credits As Credits,
Courses.Hours As Hours,
R12.PROGRAMNO as PROGRAMNO,
Courses.School As School,
Schools.Name As SchoolName,
EXAMOPTIONS.VALUE as ExamType,
R12.ExamType As ExamTypeCode,
COURSEAPPROACHES.VALUE as CourseType,
R12.CourseType As CourseTypeCode,
R12.Year as Year,
R12.Term as Term,
TESTLEVEL.VALUE AS TESTVALUE,
R12.Test As TestCode,
R12.Category As CategoryCode,
COURSECAT.VALUE AS CATEGORYVALUE,
R12.Weeks As Weeks,
'0' AS UPDATED
from R12 JOIN Courses ON R12.CourseNo=Courses.CourseNo
JOIN Programs ON R12.ProgramNo=Programs.ProgramNo
JOIN Schools ON Courses.School=Schools.School
JOIN EXAMOPTIONS ON R12.EXAMTYPE=EXAMOPTIONS.NAME
JOIN COURSEAPPROACHES  ON R12.COURSETYPE=COURSEAPPROACHES.NAME
JOIN COURSECAT ON R12.CATEGORY=COURSECAT.NAME
JOIN TESTLEVEL ON R12.TEST=TESTLEVEL.NAME
Where R12.ProgramNo =:programno
ORDER BY year,term,Courseno

 
 
 
 
