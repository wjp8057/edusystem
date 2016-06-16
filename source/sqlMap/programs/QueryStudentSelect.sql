SELECT STUDENTS.STUDENTNO,STUDENTS.NAME AS NAME,STUDENTS.CLASSNO AS CLASSNO,
STUDENTS.SCHOOL AS SCHOOL,SCHOOLS.NAME AS SCHOOLNAME,
CLASSES.CLASSNAME AS CLASSNAME
FROM STUDENTS JOIN SCHOOLS ON STUDENTS.SCHOOL=SCHOOLS.SCHOOL
JOIN CLASSES ON STUDENTS.CLASSNO=CLASSES.CLASSNO
WHERE STUDENTS.STUDENTNO LIKE :STUDENTNO
AND STUDENTS.NAME LIKE :NAME
AND STUDENTS.CLASSNO LIKE :CLASSNO
AND CLASSES.CLASSNAME LIKE :CLASSNAME
AND STUDENTS.SCHOOL LIKE :SCHOOL