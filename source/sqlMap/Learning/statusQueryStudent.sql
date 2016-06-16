SELECT
STUDENTS.STUDENTNO,
STUDENTS.NAME,
SEXCODE.NAME AS SEX,
STUDENTS.SEX AS SEXCODE,
CONVERT(varchar(10) , STUDENTS.ENTERDATE, 120 ) as ENTERDATE,
STUDENTS.YEARS,
STUDENTS.CLASSNO,
STUDENTS.TAKEN,
STUDENTS.PASSED,
STUDENTS.POINTS,
STUDENTS.REG,
STUDENTS.WARN,
STUDENTS.STATUS,
STUDENTS.CONTACT,
STUDENTS.GRADE,
STUDENTS.SCHOOL,
PERSONAL.MAJOR,
PERSONAL.BIRTHDAY,
personal.PHOTO,
CLASSES.CLASSNAME,
PERSONAL.CLASS,
SCHOOLS.NAME AS SCHOOLNAME,
STATUSOPTIONS.VALUE AS STATUSVALUE
FROM STUDENTS INNER JOIN PERSONAL ON PERSONAL.STUDENTNO=STUDENTS.STUDENTNO 
LEFT OUTER JOIN CLASSES ON STUDENTS.CLASSNO=CLASSES.CLASSNO
LEFT OUTER JOIN SCHOOLS ON STUDENTS.SCHOOL=SCHOOLS.SCHOOL
LEFT OUTER JOIN STATUSOPTIONS ON STUDENTS.STATUS=STATUSOPTIONS.NAME
LEFT OUTER JOIN SEXCODE ON STUDENTS.SEX=SEXCODE.CODE
where 
RTRIM(STUDENTS.StudentNo) =:STUDENTNO

 
