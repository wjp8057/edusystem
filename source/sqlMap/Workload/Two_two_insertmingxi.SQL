insert into work(Totalwork_id,TeacherNo,TeacherName,work,status)
select tw.Totalwork_id,tw.teacherno,tw.teacherName,case when CAST(vw.renshu as int)=0 then tw.W_number*CAST(vw.mzks as decimal(10,1))*0.9



when vw.code='M1' and cast((1+(CAST(vw.renshu as decimal(10,2))-CAST(vw.bzb as decimal(10,2)))/CAST(vw.renshu as decimal(10,2)))as decimal(10,1))<0.9 then tw.W_number*CAST(vw.mzks as decimal(10,1))*0.9
																												when vw.code='M1' and cast((1+(CAST(vw.renshu as decimal(10,2))-CAST(vw.bzb as decimal(10,2)))/CAST(vw.renshu as decimal(10,2)))as decimal(10,1))>1.6 then tw.W_number*CAST(vw.mzks as decimal(10,1))*1.6
																												when vw.code='M1' then cast(tw.W_number*CAST(vw.mzks as decimal(10,2))*(1+(CAST(vw.renshu as decimal(10,2))-CAST(vw.bzb as decimal(10,2)))/CAST(vw.renshu as decimal(10,2)))as decimal(10,1))





	when vw.code='M21' then tw.W_number*vw.mzks*0.5
																												when vw.code='M22' then CAST(vw.renshu as int)*tw.W_number*0.15
																											  when vw.code='M23' then CAST(vw.renshu as int)*tw.W_number*0.5
																											  when vw.code='M3' then CAST(vw.renshu as int)*tw.W_number*0.15
																											  when vw.code='M4' then CAST(vw.renshu as int)*2
																											  when vw.code='M5' then tw.W_number*CAST(vw.mzks as decimal(10,1))*2
																											  when vw.code='M4' then CAST(vw.renshu as int)*1.2
																									ELSE 100
end as work,1 from totalwork tw inner join
(select
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
dbo.getOne(cp.ATTENDENTS) as yjrs,
dbo.getOne(s_c.numbers) as bzb,
dbo.getOne(s_c.course_id) as course_id,
dbo.getOne(cp.SCHOOL) as SCHOOL,
sum(sp.ATTENDENTS) as renshu,
sum(c.hours) as mzks
from scheduleplan sp

INNER JOIN  teacherplan tp on sp.RECNO=tp.map
INNER JOIN COURSES c on sp.COURSENO=c.COURSENO
inner join standard_class s_c on (s_c.COURSENO=sp.COURSENO+sp.[GROUP])
LEFT JOIN TEACHERS t on t.TEACHERNO=tp.TEACHERNO
INNER JOIN COURSEPLAN cp ON (cp.COURSENO=sp.COURSENO and cp.[GROUP]=sp.[GROUP] and cp.year=:yone and cp.term=:tone)
LEFT JOIN CLASSES cl ON cp.CLASSNO=cl.CLASSNO
WHERE sp.YEAR=:ytwo and sp.TERM=:ttwo
AND sp.COURSENO+sp.[GROUP] LIKE :COURSENO AND ISNULL(cl.CLASSNO,'') LIKE :CLASSNO AND ISNULL(tp.TEACHERNO,'') like :TEACHERNO AND s_c.code LIKE :code
AND cp.SCHOOL LIKE :SCHOOL
GROUP BY sp.year,sp.term,sp.COURSENO,sp.[GROUP],tp.TEACHERNO)

 vw on(RTRIM(vw.courseno)+RTRIM(vw.gp)=RTRIM(tw.Courseno)+RTRIM(tw.[group]) and vw.TEACHERNO=tw.teacherno)
where RTRIM(tw.Totalwork_id) not in(select RTRIM(Totalwork_id) from work);



/*
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
dbo.getOne(cp.ATTENDENTS) as yjrs,
dbo.getOne(s_c.numbers) as bzb,
dbo.getOne(s_c.course_id) as course_id,
dbo.getOne(cp.SCHOOL) as SCHOOL,
sum(sp.ATTENDENTS) as renshu,
sum(tp.hours) as mzks
from scheduleplan sp

INNER JOIN  teacherplan tp on sp.RECNO=tp.map
INNER JOIN COURSES c on sp.COURSENO=c.COURSENO
inner join standard_class s_c on (s_c.COURSENO=sp.COURSENO+sp.[GROUP])
LEFT JOIN TEACHERS t on t.TEACHERNO=tp.TEACHERNO
INNER JOIN COURSEPLAN cp ON (cp.COURSENO=sp.COURSENO and cp.[GROUP]=sp.[GROUP] and cp.year=:yone and cp.term=:tone)
LEFT JOIN CLASSES cl ON cp.CLASSNO=cl.CLASSNO
WHERE sp.YEAR=:ytwo and sp.TERM=:ttwo
AND sp.COURSENO+sp.[GROUP] LIKE :COURSENO AND ISNULL(cl.CLASSNO,'') LIKE :CLASSNO AND ISNULL(tp.TEACHERNO,'') like :TEACHERNO AND s_c.code LIKE :code AND cp.SCHOOL LIKE :SCHOOL
GROUP BY sp.year,sp.term,sp.COURSENO,sp.[GROUP],tp.TEACHERNO)

 vw on(RTRIM(vw.courseno)+RTRIM(vw.gp)=RTRIM(tw.Courseno)+RTRIM(tw.[group]) and vw.TEACHERNO=tw.teacherno)
where RTRIM(tw.Totalwork_id) not in(select RTRIM(Totalwork_id) from work)*/
