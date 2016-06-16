select b.book_id,b.isbn,b.bookname,b.press,b.author,b.price,b.dis_price,count(*) num from studentbook b
where year=:year and term=:term and b.classno like :classno
group by b.book_id,b.isbn,b.bookname,b.press,b.author,b.price,b.dis_price