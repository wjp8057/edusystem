select count(*) as ROWS from(select row_number() over(ORDER BY sp.COURSENO,sp.[GROUP]) as row,
sp.COURSENO+dbo.getOne(sp.[group]) as kh,
dbo.GROUP_CONCAT_MERGE(RTRIM(cl.CLASSNAME),',') AS bj,
dbo.GROUP_CONCAT_MERGE(RTRIM(cl.CLASSNO),',') AS bjno,
dbo.getOne(isnull(tw.W_number,17)) as zs,
dbo.getOne(c.COURSENAme) as km,
dbo.getOne(t.NAME) as TEACHERNAME,
dbo.getOne(tp.TEACHERNO) as TEACHERNO,
dbo.getOne(c.type) as xklx,
dbo.getOne(cp.COURSETYPE) as kclx,
RTRIM(dbo.getOne(s_c.code)) as code,
dbo.getOne(c.CREDITS) as xf,
sum(sp.ESTIMATE) as yjrs,
dbo.getOne(s_c.numbers) as bzb,
sum(sp.ATTENDENTS) as renshu,
dbo.getOne(c.hours) as mzks
from scheduleplan sp

INNER JOIN  teacherplan tp on sp.RECNO=tp.map
INNER JOIN COURSES c on sp.COURSENO=c.COURSENO
inner join standard_class s_c on (s_c.COURSENO=sp.COURSENO+sp.[GROUP])
LEFT JOIN TEACHERS t on t.TEACHERNO=tp.TEACHERNO
INNER JOIN COURSEPLAN cp ON (cp.COURSENO=sp.COURSENO and cp.[GROUP]=sp.[GROUP] and cp.year=:yer and cp.term=:ter)
LEFT JOIN CLASSES cl ON cp.CLASSNO=cl.CLASSNO
left join totalwork tw on (RTRIM(tw.courseno)=RTRIM(sp.courseno) AND RTRIM(tw.[GROUP])=RTRIM(sp.[GROUP]) AND tw.year=:weekyear and tw.term=:weekterm and tw.TEACHERNO=tp.teacherno)
WHERE sp.YEAR=:YONE and sp.TERM=:TONE
AND sp.COURSENO+sp.[GROUP] LIKE :COURSENO AND ISNULL(cl.CLASSNO,'') LIKE :TEACHERNO AND ISNULL(tp.TEACHERNO,'') like :TEACHERNO AND s_c.code LIKE :CODE AND cp.SCHOOL LIKE :SCHOOL
and not exists(select Courseno from totalwork
where rtrim(sp.courseno)+rtrim(sp.[group])=rtrim(totalwork.courseno)+rtrim(totalwork.[group])
and sp.year=:syear
and sp.term=:sterm
and totalwork.year=:tyear and totalwork.term=:tterm and totalwork.status!=1)
GROUP BY sp.COURSENO,sp.[GROUP],tp.TEACHERNO
) as cc