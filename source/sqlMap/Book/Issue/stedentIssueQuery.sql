select r32.studentno,s.name from bookapply b left join r32 on r32.year=b.year
and r32.term=b.term and r32.courseno=b.courseno and b.[group]=r32.[group]
left join students s on r32.studentno=s.studentno
where r32.year=:year and r32.term=:term and not exists (select * from bookpayment p
where p.year=b.year and p.term=b.term and p.book_id=b.book_id and r32.studentno=p.no and p.type=0)
and b.apply_id=:apply_id