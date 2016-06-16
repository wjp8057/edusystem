SELECT SCHEDULE.TIME+SCHEDULE.DAY+SCHEDULE.OEW AS TIME,
RTRIM(COURSES.COURSENAME)+':'+SCHEDULE.COURSENO+SCHEDULE.[GROUP]+'('+RTRIM(CLASSROOMS.JSN)+')'+TEACHERS.NAME AS COURSE,
SCHEDULE.WEEKS,
TASKOPTIONS.NAME AS TASK
FROM SCHEDULE JOIN R32
ON SCHEDULE.COURSENO=R32.COURSENO
AND SCHEDULE.[GROUP]=R32.[GROUP]
AND SCHEDULE.YEAR=R32.YEAR
AND SCHEDULE.TERM=R32.TERM
JOIN COURSES
ON SCHEDULE.COURSENO=COURSES.COURSENO
JOIN CLASSROOMS ON SCHEDULE.ROOMNO=CLASSROOMS.ROOMNO
JOIN TEACHERPLAN ON SCHEDULE.MAP=TEACHERPLAN.RECNO
JOIN TEACHERS ON TEACHERPLAN.TEACHERNO=TEACHERS.TEACHERNO
LEFT OUTER JOIN TASKOPTIONS ON TEACHERPLAN.TASK=TASKOPTIONS.CODE
WHERE SCHEDULE.YEAR=:YEAR
AND SCHEDULE.TERM=:TERM
AND R32.STUDENTNO=:STUDENTNO
ORDER BY TIME