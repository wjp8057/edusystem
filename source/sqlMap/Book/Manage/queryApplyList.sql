select b.apply_id,b.courseno+b.[group] courseno,c.coursename,c2.[value],b.attendents,s.name schoolname,b2.isbn,b2.bookname,b.approaches,
b.stu_quantity,b.tea_quantity,b.status,
convert(varchar(10),b.booktime,23) booktime 
from bookapply b 
left join courses c on b.courseno=c.courseno  
left join courseapproaches c2 on c2.name=b.approaches 
left join classes c3 on c3.classno=b.classno 
left join schools s on b.school=s.school 
left join book b2 on b2.book_id=b.book_id 
where b.[year] = :year and b.term = :term and b.approaches like :approaches and b.courseno+b.[group] like :courseno and c.coursename like :coursename 
and b.school like :school and b.classno like :classno and b.status like :status 