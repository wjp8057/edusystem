SELECT COUNT(*) FROM(SELECT TEACHERNO FROM WZJJSZG WHERE YEAR =:YEAR AND TERM =:TERM AND SCHOOL LIKE :SCHOOL GROUP BY YEAR,TERM,SCHOOL,SCHOOLNAME,TEACHERNO,TEACHERNAME,P_VALUE,JB,ZhuJiangZhiGe,COURSENO,COURSENAME,VALUE,CREDITS,
HOURS,EXPERIMENTS,COMPUTING) T