select * from(select
row_number() over(order by dbo.getOne(t.teacherno)) as row,
dbo.getOne(tschool.name) as tname,
dbo.getOne(cschool.name) as cname,
dbo.getOne(t.teacherno) as teacherno,
dbo.getOne(t.teachername) as teachername,
dbo.getOne(SEXCODE.name) as sex,
dbo.getOne(POSITIONS.[value]) as zc,
dbo.getOne(TEACHERTYPE.[value]) as gwlx,
cast(sum(w.work*w.cfxs) as decimal(18,2)) as work
from totalwork t left join schools tschool on t.teacherschool=tschool.school
inner join work w on t.Totalwork_id=w.Totalwork_id
left join courses on t.courseno=COURSES.courseno
left join teachers on teachers.teacherno=t.teacherno
left join SEXCODE on teachers.sex = SEXCODE.CODE
left join POSITIONS on teachers.position=POSITIONS.NAME
left join TEACHERTYPE on teachers.type=TEACHERTYPE.NAME
left join (select * from schools) cschool on COURSES.school=cschool.school
group by t.teacherno,cschool.school
having dbo.getOne(t.code) like :code and dbo.getOne(t.teachername) like :name
and dbo.getOne(t.teacherschool) like :school
) as b
