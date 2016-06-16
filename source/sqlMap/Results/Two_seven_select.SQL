select * from(select row_number() over(order by temp.name) as row,temp.name as zy,temp.total cjrs,isnull(temp3.pass,0) tgrs from (select majorcode.name,count(*) total from personal
inner join students on students.studentno=personal.studentno
inner join majorcode on majorcode.code=personal.major
inner join
(
select distinct 学号 from 计算机统考成绩
where 等级 like '%一级%'
group by 学号
) as temp2 on temp2.学号=students.studentno
where substring(students.classno,1,2)=:GONE and students.studentno not like 'PT%'
group by majorcode.name ) as temp
left outer join (
select majorcode.name,count(*) pass from personal
inner join students on students.studentno=personal.studentno
inner join majorcode on majorcode.code=personal.major
inner join
(
select distinct 学号 from 计算机统考成绩
where 等级 like '%一级%'
group by 学号
having max(成绩)>=60
) as temp2 on temp2.学号=students.studentno
where substring(students.classno,1,2)=:GTWO and students.studentno not like 'PT%'
group by majorcode.name
) as temp3 on temp3.name=temp.name
) as b