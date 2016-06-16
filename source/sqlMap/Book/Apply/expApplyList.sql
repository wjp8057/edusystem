select b.courseno+b.[group] courseno,c.coursename,c2.[value] coursetype,c3.classname,b.attendents,
teachername=stuff((select DISTINCT '/'+ rtrim(name) from teacherplan t left join teachers t2 on t.teacherno=t2.teacherno where t.map=b.map FOR XML PATH('')), 1, 1, ''),
s.name schoolname,b2.isbn,b2.bookname,b2.author,p.name press,
case when b.status='0' then '未征订' when b.status='1' then '已征订' when b.status='2' then '暂不征订' end status,convert(varchar(10),b.booktime,23) booktime  
from bookapply b 
left join book b2 on b2.book_id=b.book_id 
left join bookpress p on p.id=b2.press 
left join courses c on b.courseno=c.courseno  
left join courseapproaches c2 on c2.name=b.approaches 
left join classes c3 on c3.classno=b.classno 
left join schools s on b.school=s.school 
where b.[year] = :YEAR and b.term = :TERM and b.approaches like :COURSETYPE and b.courseno+b.[group] like :COURSENO and c.coursename like :COURSENAME 
and b.classno like :CLASSNO and b.school like :SCHOOL and b.status like :STATUS
and b.map in (select t.map from teacherplan t left join teachers t2 on t.teacherno=t2.teacherno where t2.name like :TEACHERNAME)
order by s.name,b.courseno