select b.apply_id,b.courseno+b.[group] courseno,c.coursename,c2.[value],b.attendents,b.stu_quantity,s.name,b.tea_quantity,b.status,convert(varchar(10),b.booktime,23) booktime,b.createdate
from bookapply b 
left join courses c on b.courseno=c.courseno  
left join courseapproaches c2 on c2.name=b.approaches 
left join classes c3 on c3.classno=b.classno 
left join schools s on b.school=s.school 
where b.[year] = :YEAR and b.term = :TERM and b.approaches like :COURSETYPE and b.courseno+b.[group] like :COURSENO and c.coursename like :COURSENAME 
and b.classno like :CLASSNO and b.school like :SCHOOL and b.status like :STATUS
and b.map in (select t.map from teacherplan t left join teachers t2 on t.teacherno=t2.teacherno where t2.name like :TEACHERNAME)
order by b.createdate desc