select count(*) from (select RTRIM(students.studentno) As StudentNo,
students.name As Name,
students.sex As Sex,
classes.classname As ClassName,
students.classno As ClassNo,
students.Points As Points,
personal.score As Scores,
schools.name As SchoolName,
classes.school as ClassSchool,
RTRIM(SEXCODE.NAME) AS SEXNAME,
STATUSOPTIONS.VALUE as Status,
'' AS SL,0 AS AMOUNT
from students JOIN CLASSES ON students.classno=classes.classno
JOIN personal ON students.studentno=personal.studentno
JOIN schools ON students.school=schools.school
inner join statusoptions on statusoptions.NAME=students.STATUS
LEFT OUTER JOIN SEXCODE ON STUDENTS.SEX=RTRIM(SEXCODE.CODE)
where Classes.ClassNo=:ClassNo)as b
