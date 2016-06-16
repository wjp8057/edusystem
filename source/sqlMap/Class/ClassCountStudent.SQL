select Count(students.studentNo) As ROWS
from students JOIN personal ON students.studentno=personal.studentno
JOIN schools ON students.school=schools.school
LEFT OUTER JOIN classes ON students.classno=classes.classno
LEFT OUTER JOIN SEXCODE ON STUDENTS.SEX=SEXCODE.CODE
WHERE students.studentno like :StudentNo and students.Name like :Name
and students.school Like :School and students.classno like :ClassNo
