select * from (select row_number() over(order by Students.StudentNo)as row,Students.StudentNo As StudentNo,
Students.Name As Name,
Students.Sex As Sex,
SEXCODE.NAME AS SEXNAME,
Students.Points As Points,
Personal.Score As Scores ,
Students.ClassNo As ClassNo,
Schools.Name As SchoolName,
Classes.ClassName As ClassName,
schools.school as schoolno
from students JOIN personal ON students.studentno=personal.studentno
JOIN schools ON students.school=schools.school
LEFT OUTER JOIN classes ON students.classno=classes.classno
LEFT OUTER JOIN SEXCODE ON STUDENTS.SEX=SEXCODE.CODE
where  students.studentno like :StudentNo
and students.Name like :Name
and students.school Like :School
and (students.classno like :ClassNo))as b where b.row between :start and :end
