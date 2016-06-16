select * from (select row_number() over(order by t.teacherno) as row,t.teacherno,t.courseno,coursename,schools.name as school,task,t1.total,isnull(compelete,0) as compelete,
o.NAME typename,teachers.name as teachername
 from 教学质量评估综合 as t
inner join (select map,count(*) as total from  教学质量评估详细
group by map ) as t1 on t1.map=t.recno
left join (select map,count(*) as compelete from 教学质量评估详细
where Compelete=1
group by map ) as t2 on t2.map=t.recno
inner join courses on courses.courseno=substring(t.courseno,1,7)
inner join schools on schools.school=courses.school
inner join teachers on teachers.teacherno=t.teacherno
inner join 教学质量评估类型 as o on o.type=t.task
where t.year=:year and term=:term and enabled=1
and t.courseno like :courseno and coursename like :coursename and teachers.name like :name
) as b where b.row between :start and :end