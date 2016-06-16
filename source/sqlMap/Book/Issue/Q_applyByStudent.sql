select b.book_id,b.isbn,b.bookname,p.name press,b.author,c.classname,p2.price,p2.dis_price,p2.dis_rate from bookapply a 
left join book b on a.book_id=b.book_id 
left join bookpress p on p.id=b.press 
left join classes c on c.classno=a.classno 
left join bookprice p2 on p2.book_id=b.book_id and a.[year]=p2.[year] and a.term=p2.term 
where (a.status=1 or a.status=3) and a.classno like :classno and a.[year] = :year and a.term = :term
group by b.book_id,b.isbn,b.bookname,p.name,b.author,c.classname,p2.price,p2.dis_price,p2.dis_rate