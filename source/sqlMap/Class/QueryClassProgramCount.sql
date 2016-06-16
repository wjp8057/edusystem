SELECT COUNT(*) AS ROWS
FROM PROGRAMS
JOIN SCHOOLS ON PROGRAMS.SCHOOL=SCHOOLS.SCHOOL
LEFT OUTER JOIN PROGRAMTYPE ON PROGRAMS.TYPE=PROGRAMTYPE.NAME
WHERE PROGRAMS.PROGRAMNO LIKE :PROGRAMNO
AND PROGRAMS.PROGNAME LIKE :PROGRAMNAME
AND PROGRAMS.SCHOOL LIKE :SCHOOL

