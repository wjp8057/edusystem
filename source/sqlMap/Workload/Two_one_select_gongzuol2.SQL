select

dbo.getOne(s_c.course_id) as course_id

from scheduleplan sp
LEFT JOIN (select SCHEDULEPLAN.courseno+SCHEDULEPLAN.[group] as coruseno,COUNT(R32.STUDENTNO) as rs from SCHEDULEPLAN inner join R32 on(SCHEDULEPLAN.COURSENO+SCHEDULEPLAN.[GROUP]=R32.COURSENO+R32.[GROUP]  AND R32.YEAR=:ylin AND R32.TERM=:tlin) WHERE SCHEDULEPLAN.YEAR=:yyi AND SCHEDULEPLAN.TERM=:tyi GROUP BY SCHEDULEPLAN.courseno,SCHEDULEPLAN.[group]) as aia
ON aia.coruseno+aia.[group]=sp.courseno+sp.[group]
INNER JOIN  teacherplan tp on sp.RECNO=tp.map
INNER JOIN COURSES c on sp.COURSENO=c.COURSENO
inner join standard_class s_c on (s_c.COURSENO=sp.COURSENO+sp.[GROUP]) 
LEFT JOIN TEACHERS t on t.TEACHERNO=tp.TEACHERNO 
INNER JOIN COURSEPLAN cp ON (cp.COURSENO=sp.COURSENO and cp.[GROUP]=sp.[GROUP] and cp.year=:yer and cp.term=:ter)
LEFT JOIN CLASSES cl ON cp.CLASSNO=cl.CLASSNO
WHERE sp.YEAR=:YONE and sp.TERM=:TONE
AND sp.COURSENO+sp.[GROUP] LIKE :COURSENO AND ISNULL(cl.CLASSNO,'') LIKE :TEACHERNO AND ISNULL(tp.TEACHERNO,'') like :TEACHERNO AND s_c.code LIKE :CODE AND cp.SCHOOL LIKE :SCHOOL
GROUP BY sp.COURSENO,sp.[GROUP],tp.TEACHERNO
