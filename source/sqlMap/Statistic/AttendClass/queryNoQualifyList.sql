SELECT YEAR,TERM,SCHOOL,SCHOOLNAME,TEACHERNO,TEACHERNAME,P_VALUE,JB,ZhuJiangZhiGe,COURSENO,COURSENAME,VALUE,CREDITS,
	HOURS,EXPERIMENTS,COMPUTING,
	CLASSNO=STUFF((SELECT '/'+CLASSNO FROM WZJJSZG T WHERE T.YEAR=N.YEAR AND T.TERM=N.TERM AND 
	T.TEACHERNO = N.TEACHERNO AND T.COURSENO=N.COURSENO AND T.VALUE=N.VALUE FOR XML PATH('')), 1, 1, ''),
	CLASSNAME=STUFF((SELECT '/'+CLASSNAME FROM WZJJSZG T WHERE T.YEAR=N.YEAR AND T.TERM=N.TERM AND 
	T.TEACHERNO = N.TEACHERNO AND T.COURSENO=N.COURSENO AND T.VALUE=N.VALUE FOR XML PATH('')), 1, 1, '') FROM WZJJSZG N
WHERE YEAR =:YEAR AND TERM =:TERM AND SCHOOL LIKE :SCHOOL 
GROUP BY YEAR,TERM,SCHOOL,SCHOOLNAME,TEACHERNO,TEACHERNAME,P_VALUE,JB,ZhuJiangZhiGe,COURSENO,COURSENAME,VALUE,CREDITS,
HOURS,EXPERIMENTS,COMPUTING ORDER BY SCHOOL,COURSENO