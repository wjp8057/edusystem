select count(*) as ROWS from(select row_number() over(order by SCHEDULEPLAN.courseno+SCHEDULEPLAN.[group]) as row,courses.coursename as km,
SCHEDULEPLAN.courseno+SCHEDULEPLAN.[group] as kh,
courses.CREDITS as xf,
17 as zs,
clas.CLASSNAME,
clas.CLASSNO,
courses.type as xklx,
b.hours as mzks,
standard_class.numbers as bzb,
b.NAME as TEACHERNAME,
b.TEACHERNO,
standard_class.code as bxxs
from scheduleplan left join courses on scheduleplan.courseno=courses.courseno
left join standard_class  on standard_class.courseno=scheduleplan.courseno+scheduleplan.[group]

left join (select COURSEPLAN.SCHOOL,COURSEPLAN.COURSENO+COURSEPLAN.[GROUP] as COURSENO,dbo.GROUP_CONCAT_MERGE(CLASSES.CLASSNAME,',') AS CLASSNAME,
dbo.GROUP_CONCAT_MERGE(COURSEPLAN.CLASSNO,',') as CLASSNO 
from COURSEPLAN left join CLASSES ON (CLASSES.CLASSNO=COURSEPLAN.CLASSNO )
WHERE COURSEPLAN.YEAR=:YONE AND COURSEPLAN.TERM=:TONE
 group by COURSEPLAN.COURSENO,COURSEPLAN.[GROUP],COURSEPLAN.SCHOOL) AS clas
ON  SCHEDULEPLAN.courseno+SCHEDULEPLAN.[group]=clas.COURSENO

left join (select TEACHERPLAN.TEACHERNO,TEACHERS.NAME,sum(TEACHERPLAN.hours) AS hours,TEACHERPLAN.map from teacherplan left join TEACHERS on TEACHERPLAN.teacherno=TEACHERS.teacherno where TEACHERPLAN.year=:YTWO and TEACHERPLAN.term=:TTWO and TEACHERPLAN.toschedule=1 group by TEACHERPLAN.teacherno,teacherplan.map,TEACHERS.NAME) as b
on b.map=scheduleplan.recno
left join r32 on R32.courseno=SCHEDULEPLAN.courseno+SCHEDULEPLAN.[group]
WHERE SCHEDULEPLAN.YEAR=:YTHREE AND SCHEDULEPLAN.TERM=:TTHREE
AND SCHEDULEPLAN.COURSENO+SCHEDULEPLAN.[GROUP] LIKE :COURSENO AND ISNULL(clas.CLASSNO,'') LIKE :TEACHERNO AND ISNULL(b.TEACHERNO,'') like :TEACHERNO AND standard_class.code LIKE :CODE AND clas.SCHOOL LIKE :SCHOOL
) as cc

