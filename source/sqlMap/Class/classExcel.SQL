select Classes.ClassNo As CLASSNO,
Classes.ClassName As CLASSNAME,
Classes.School As SCHOOL,
Classes.Students As STUDENTS,
CONVERT(varchar(10),Classes.YEAR,20) As YEAR,
Schools.Name As SCHOOLNAME,
MYCOUNT.COUNTS AS COUNTS,
'' AS CL
from Classes
JOIN Schools ON Classes.School=Schools.School
JOIN
(
    select CLASSES.CLASSNO AS CLASSNO,Count(students.studentNo) As COUNTS
    from CLASSES
    LEFT OUTER JOIN STUDENTS ON CLASSES.classno=STUDENTS.classno
    JOIN schools ON CLASSES.school=schools.school
    GROUP BY CLASSES.CLASSNO
) AS MYCOUNT ON CLASSES.CLASSNO=MYCOUNT.CLASSNO
WHERE Classes.ClassNo like :CLASSNO
and Classes.ClassName like :CLASSNAME
and Classes.School like :SCHOOL
and CAST(YEAR(Classes.YEAR) AS CHAR) LIKE :YEAR
ORDER BY CLASSES.CLASSNO