select students.StudentNo As STUDENTNO,
students.Name As NAME,
Classes.ClassNo As CLASSNO,
Classes.ClassName as CLASSNAME,
STUDENTS.SCHOOL AS SCHOOL,
Schools.Name As SCHOOLNAME
from students JOIN R28 ON students.StudentNo=R28.StudentNo
JOIN SCHOOLS ON STUDENTS.SCHOOL=SCHOOLS.SCHOOL
JOIN CLASSES ON STUDENTS.CLASSNO=CLASSES.CLASSNO
where
R28.ProgramNo=:programno
 
 
 
 
