insert into totalwork(year,Term,Courseno,[group],Coursename,W_Hours,W_number,Standard,Estimate,Attendent,Classname,status,school,teachername,classno,teacherno,credit,kclx,xklx,mzks)
select year,term,courseno,gp,km,mzks,17,bzb,yjrs,renshu,bj,1,SCHOOL,TEACHERNAME,CLASSNO,TEACHERNO,xf,kclx,xklx,mzks FROM (
select
sp.year,
sp.term,
sp.COURSENO,
dbo.getOne(sp.[group]) as gp,
dbo.GROUP_CONCAT_MERGE(RTRIM(cl.CLASSNAME),',') AS bj,
dbo.GROUP_CONCAT_MERGE(RTRIM(cl.CLASSNO),',') AS CLASSNO,
17 as zs,
dbo.getOne(c.COURSENAme) as km,
dbo.getOne(t.NAME) as TEACHERNAME,
dbo.getOne(tp.TEACHERNO) as TEACHERNO,
dbo.getOne(c.type) as xklx,
dbo.getOne(cp.COURSETYPE) as kclx,
dbo.getOne(s_c.code) as code,
dbo.getOne(c.CREDITS) as xf,
sum(sp.ESTIMATE) as yjrs,
dbo.getOne(s_c.numbers) as bzb,
dbo.getOne(s_c.course_id) as course_id,
dbo.getOne(cp.SCHOOL) as SCHOOL,
sum(sp.ATTENDENTS) as renshu,
dbo.getOne(c.hours) as mzks
from scheduleplan sp

INNER JOIN  teacherplan tp on sp.RECNO=tp.map
INNER JOIN COURSES c on sp.COURSENO=c.COURSENO
inner join standard_class s_c on (s_c.COURSENO=sp.COURSENO+sp.[GROUP])
LEFT JOIN TEACHERS t on t.TEACHERNO=tp.TEACHERNO
INNER JOIN COURSEPLAN cp ON (cp.COURSENO=sp.COURSENO and cp.[GROUP]=sp.[GROUP] and cp.year=:yone and cp.term=:tone)
LEFT JOIN CLASSES cl ON cp.CLASSNO=cl.CLASSNO
WHERE sp.YEAR=:ytwo and sp.TERM=:ttwo
AND sp.COURSENO+sp.[GROUP] LIKE :COURSENO AND ISNULL(cl.CLASSNO,'') LIKE :CLASSNO AND ISNULL(tp.TEACHERNO,'') like :TEACHERNO AND s_c.code LIKE :code AND cp.SCHOOL LIKE :SCHOOL
GROUP BY sp.year,sp.term,sp.COURSENO,sp.[GROUP],tp.TEACHERNO
) as VIEW_ALLWORKSa WHERE RTRIM(VIEW_ALLWORKSa.courseno)+RTRIM(VIEW_ALLWORKSa.gp)+RTRIM(VIEW_ALLWORKSa.TEACHERNO)+RTRIM(VIEW_ALLWORKSa.year)+RTRIM(VIEW_ALLWORKSa.term) NOT IN(select RTRIM(Courseno)+RTRIM([group])+RTRIM(teacherno)+RTRIM(year)+RTRIM(term) from totalwork
);
