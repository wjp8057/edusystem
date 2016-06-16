select b.apply_id,c.courseno+b.[group] courseno,c.coursename,
case when b.classno='000000' then '公共选修' else cs.classname end classname,
case when cs.students is null then 0 else cs.students end students,
b.oderno,b2.isbn,b2.bookname,b2.author,p.name press,c2.[value] approaches,
b.stu_quantity+b.tea_issue_nym [sum],
teachername=stuff((select DISTINCT '/'+ rtrim(name) from teacherplan t left join teachers t2 on t.teacherno=t2.teacherno where t.map=b.map FOR XML PATH('')), 1, 1, ''),
s.name,b.school,b.map,b.classno
from bookapply b 
left join schools s on b.school=s.school 
left join book b2 on b2.book_id=b.book_id 
left join courses c on c.courseno=b.courseno 
left join courseapproaches c2 on c2.name=b.approaches 
left join classes cs on cs.classno=b.classno 
left join bookpress p on p.id=b2.press 
where b.[year] = :year and b.term = :term and b.school like :school and b.status = '0' 
order by b.courseno 