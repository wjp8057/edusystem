select b.*,p.[value] status from studentbook b
left join students stu on stu.studentno=b.studentno
left join statusoptions p on p.name=stu.status
where b.[year]=:year and b.term=:term and b.book_id=:book_id and b.[name] like :name and  b.studentno like :studentno and  b.classno like :classno
order by b.studentno