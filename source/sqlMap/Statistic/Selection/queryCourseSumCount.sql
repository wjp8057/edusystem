SELECT  S2.SCHOOL
	FROM COURSES C INNER JOIN SCHEDULEPLAN S ON C.COURSENO = S.COURSENO 
	INNER JOIN COURSETYPEOPTIONS C2 ON C.TYPE = C2.NAME INNER JOIN SCHOOLS S2 ON C.SCHOOL = S2.SCHOOL
	WHERE S.YEAR =:YEAR AND S.TERM =:TERM AND S2.SCHOOL LIKE :SCHOOL
	GROUP BY C2.VALUE,S2.NAME,S2.SCHOOL