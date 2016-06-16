update standard_class set numbers=:numbers where course_id in(select
dbo.getOne(s_c.course_id) as course_id
from scheduleplan sp
INNER JOIN  teacherplan tp on sp.RECNO=tp.map
INNER JOIN COURSES c on sp.COURSENO=c.COURSENO
inner join standard_class s_c on (s_c.COURSENO=sp.COURSENO+sp.[GROUP])
LEFT JOIN TEACHERS t on t.TEACHERNO=tp.TEACHERNO
INNER JOIN COURSEPLAN cp ON (cp.COURSENO=sp.COURSENO and cp.[GROUP]=sp.[GROUP])
LEFT JOIN CLASSES cl ON cp.CLASSNO=cl.CLASSNO
WHERE sp.YEAR=:YONE and sp.TERM=:TONE
AND sp.COURSENO+sp.[GROUP] LIKE :COURSENO AND ISNULL(cl.CLASSNO,'') LIKE :TEACHERNO AND ISNULL(tp.TEACHERNO,'') like :TEACHERNO AND s_c.code LIKE :CODE AND cp.SCHOOL LIKE :SCHOOL
GROUP BY sp.COURSENO,sp.[GROUP],tp.TEACHERNO
)