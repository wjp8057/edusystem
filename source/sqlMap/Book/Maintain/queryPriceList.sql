select p.[year],p.term,b.isbn,b.bookname,b.author,bp.name press,b.booknature,
case when p.price is null then 0 else p.price end price,
case when p.dis_rate is null then b.dis_rate else p.dis_rate end dis_rate,
p.dis_price,b.book_id,p.id from book b left join bookprice p on b.book_id=p.book_id 
left join bookpress bp on bp.id=b.press 
where b.isbn like :isbn and b.bookname like :bookname and b.booknature like :booknature 