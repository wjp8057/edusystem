SELECT count(*)
FROM STUDENTS
LEFT JOIN CLASSES
ON STUDENTS.CLASSNO=CLASSES.CLASSNO
LEFT OUTER JOIN SCHOOLS
ON STUDENTS.SCHOOL=SCHOOLS.SCHOOL
WHERE :FIELDSNAME LIKE :FIELDSVALUE