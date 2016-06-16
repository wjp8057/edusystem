select c3.classname,c.courseno,c.coursename,c2.[value],b.oderno,b2.isbn,b2.bookname,b2.author,p1.name press,b.issue_nym,
p.price,p.dis_rate,p.dis_price,(p.price * issue_nym) m_price,(p.dis_price * issue_nym) s_price,s.name schoolname,
teachername=stuff((select DISTINCT '/'+ rtrim(name) from teacherplan t left join teachers t2 on t.teacherno=t2.teacherno
 where t.map=b.map FOR XML PATH('')), 1, 1, '') 
from bookapply b 
left join courses c on b.courseno=c.courseno  
left join courseapproaches c2 on c2.name=b.approaches 
left join schools s on b.school=s.school 
left join book b2 on b2.book_id=b.book_id 
left join bookpress p1 on b2.press=p1.id 
left join classes c3 on c3.classno=b.classno 
left join bookprice p on p.book_id = b.book_id and p.[year]=b.[year] and p.term =b.term 
where b.[year] = :year and b.term = :term and b.courseno+b.[group] like :courseno and c.coursename like :coursename 
and b.school like :school and b.classno like :classno and b.status='3' and b.issue_nym > 0