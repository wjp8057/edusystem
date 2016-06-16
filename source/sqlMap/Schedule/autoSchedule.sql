SELECT
scheduleplan.YEAR,
scheduleplan.TERM,
scheduleplan.COURSENO,
scheduleplan.[GROUP],
scheduleplan.ESTIMATE,
scheduleplan.ATTENDENTS,
scheduleplan.ROOMTYPE,
scheduleplan.LHOURS,
scheduleplan.EHOURS,
scheduleplan.WEEKS,
scheduleplan.RECNO AS SCHEDULEPLANRECNO,
scheduleplan.EMPROOM,
scheduleplan.TIME,
scheduleplan.SCHEDULED,
scheduleplan.EBATCH,
scheduleplan.DAYS,
scheduleplan.AREA,
teacherplan.TEACHERNO,
teacherplan.HOURS,
teacherplan.UNIT,
teacherplan.REM,
teacherplan.TASK,
teacherplan.AMOUNT,
teacherplan.TOSCHEDULE,
teacherplan.RECNO,
courses.TYPE
FROM SCHEDULEPLAN LEFT OUTER JOIN TEACHERPLAN ON SCHEDULEPLAN.RECNO=TEACHERPLAN.MAP
JOIN COURSES ON SCHEDULEPLAN.COURSENO=COURSES.COURSENO
WHERE scheduleplan.YEAR=:YEAR AND scheduleplan.TERM=:TERM
AND teacherplan.TEACHERNO is not null AND teacherplan.TEACHERNO<>''
AND NOT EXISTS (
	SELECT SCHEDULE.RECNO from SCHEDULE WHERE SCHEDULEPLAN.[YEAR] = SCHEDULE.[YEAR] AND SCHEDULEPLAN.TERM = SCHEDULE.TERM
	AND SCHEDULEPLAN.COURSENO=SCHEDULE.COURSENO AND SCHEDULEPLAN.[GROUP]=SCHEDULE.[GROUP]
	)
ORDER BY scheduleplan.COURSENO,scheduleplan.[GROUP]