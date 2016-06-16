SELECT SUBSIDIARIES.CLASSNO,
SUBSIDIARIES.CLASSNAME,
SUBSIDIARIES.SCHOOL,
SCHOOLS.NAME AS SCHOOLNAME
FROM SUBSIDIARIES
JOIN SCHOOLS ON SUBSIDIARIES.SCHOOL=SCHOOLS.SCHOOL
WHERE SUBSIDIARIES.CLASSNO LIKE :CLASSNO
AND SUBSIDIARIES.CLASSNAME LIKE :CLASSNAME
AND SUBSIDIARIES.SCHOOL LIKE :SCHOOL