insert into totalwork(year,Term,Courseno,[group],Coursename,W_Hours,W_number,Standard,Estimate,Attendent,Classname,status,school,teachername,
classno,teacherno,credit,kclx,xklx,mzks,code)
select
sp.COURSENO,
dbo.getOne(sp.[group]) as gp,
dbo.getOne(c.COURSENAme) as km,
sum(c.hours) as mzks,
17,
50,
sum(sp.ESTIMATE) as yjrs,
sum(sp.ATTENDENTS) as renshu,
dbo.GROUP_CONCAT_MERGE(RTRIM(cl.CLASSNAME),',') AS bj,
1,
dbo.getOne(cp.SCHOOL) as SCHOOL,
dbo.getOne(t.NAME) as TEACHERNAME,
dbo.GROUP_CONCAT_MERGE(RTRIM(cl.CLASSNO),',') AS bjno,
dbo.getOne(tp.TEACHERNO) as TEACHERNO,
dbo.getOne(c.CREDITS) as xf,
dbo.getOne(COURSEAPPROACHES.VALUE) as kclx,
dbo.getOne(COURSETYPEOPTIONS.[VALUE]) as xklx,
mzks,
'M1' as code,
from scheduleplan sp
INNER JOIN  teacherplan tp on sp.RECNO=tp.map
INNER JOIN COURSES c on sp.COURSENO=c.COURSENO
LEFT JOIN TEACHERS t on t.TEACHERNO=tp.TEACHERNO
INNER JOIN COURSEPLAN cp ON (cp.COURSENO=sp.COURSENO and cp.[GROUP]=sp.[GROUP] and cp.year=:yer and cp.term=:ter)
left JOIN COURSEAPPROACHES ON COURSEAPPROACHES.NAME=cp.COURSETYPE
left JOIN COURSETYPEOPTIONS ON c.TYPE=COURSETYPEOPTIONS.NAME
LEFT JOIN CLASSES cl ON cp.CLASSNO=cl.CLASSNO
left join totalwork tw on
(RTRIM(tw.courseno)=RTRIM(sp.courseno) AND RTRIM(tw.[GROUP])=RTRIM(sp.[GROUP]) AND tw.year=:weekyear and tw.term=:weekterm and tw.TEACHERNO=tp.teacherno)
WHERE sp.YEAR=:YONE and sp.TERM=:TONE
AND sp.COURSENO+sp.[GROUP] LIKE :COURSENO AND ISNULL(cl.CLASSNO,'') LIKE :TEACHERNO AND ISNULL(tp.TEACHERNO,'') like :TEACHERNO  AND cp.SCHOOL LIKE :SCHOOL
and not exists(select Courseno from totalwork
where rtrim(sp.courseno)+rtrim(sp.[group])=rtrim(totalwork.courseno)+rtrim(totalwork.[group])
and sp.year=:syear
and sp.term=:sterm
and totalwork.year=:tyear and totalwork.term=:tterm and totalwork.status!=1)
GROUP BY sp.COURSENO,sp.[GROUP],tp.TEACHERNO
