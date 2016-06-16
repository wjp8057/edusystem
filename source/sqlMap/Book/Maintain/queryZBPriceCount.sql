select count(*) from (select p.[year],p.term,b.isbn,b.bookname,b.author,bp.name press,b.booknature,
case when p.price is null then 0 else p.price end price,
case when p.dis_rate is null then b.dis_rate else p.dis_rate end dis_rate,
p.dis_price,b.book_id,p.id from book b left join bookprice p on b.book_id=p.book_id 
left join bookpress bp on bp.id=b.press 
where b.booknature like '自编' and p.[year]=:year and p.term=:term 
union all 
select [year]=null,term=null,b.isbn,b.bookname,b.author,p.name press,b.booknature,
price=null,dis_rate,dis_price=null,b.book_id,id=null from book b 
left join bookpress p on p.id=b.press 
where b.booknature like '自编' and b.book_id not in (select book_id from bookprice where [year]=:n_year and term=:n_term)) b 
where isbn like :isbn and bookname like :bookname 