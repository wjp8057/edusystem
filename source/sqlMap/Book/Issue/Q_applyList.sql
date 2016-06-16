select b.apply_id,b.courseno+b.[group] courseno,c.coursename,c2.[value],b.attendents,s.name schoolname,b2.isbn,b2.bookname,b2.author,b.stu_quantity,p.price,
case when p.dis_rate is null then b2.dis_rate else p.dis_rate end dis_rate,
p.dis_price,(select count(p.payment_id) from bookpayment p where p.apply_id=b.apply_id and p.type='0') issue_nym,
b.book_id,b.oderno,b.classno,b.[year],b.term,b.courseno course,b.map,b.[group],b.status from bookapply b 
left join courses c on b.courseno=c.courseno  
left join courseapproaches c2 on c2.name=b.approaches 
left join schools s on b.school=s.school 
left join book b2 on b2.book_id=b.book_id 
left join bookprice p on p.book_id = b.book_id and p.[year]=b.[year] and p.term =b.term 
where b.[year] = :year and b.term = :term and b.courseno+b.[group] like :courseno and c.coursename like :coursename 
and b.school like :school and b.classno like :classno