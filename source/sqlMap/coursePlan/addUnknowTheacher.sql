INSERT INTO TEACHERPLAN(YEAR,TERM,MAP,HOURS,TASK,AMOUNT)
SELECT YEAR,TERM,RECNO,0,'L',0 FROM SCHEDULEPLAN WHERE YEAR=:YEAR AND TERM=:TERM
AND NOT EXISTS (SELECT * FROM TEACHERPLAN WHERE TEACHERPLAN.MAP=SCHEDULEPLAN.RECNO)