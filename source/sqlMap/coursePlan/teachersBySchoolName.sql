
SELECT RTRIM(TEACHERS.TEACHERNO) AS CODE,RTRIM(TEACHERS.NAME) AS NAME FROM TEACHERS,SCHOOLS
WHERE TEACHERS.SCHOOL=SCHOOLS.SCHOOL AND SCHOOLS.NAME LIKE :SCHOOLNAME ORDER BY NAME