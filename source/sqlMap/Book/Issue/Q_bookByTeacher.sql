select b.payment_id,b.isbn,b.bookname,b.press,b.author,b.price,b.dis_rate,b.dis_price from teacherbook b
where b.[year]=:year and b.term=:term and b.teacherno like :teacherno order by isbn