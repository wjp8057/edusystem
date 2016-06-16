select bookname,price,dis_price,dis_rate,count(*) number,author,press,book_id from studentbook bs 
where bs.studentno=:studentno and [year]=:year and term=:term 
group by bookname,author,press,price,dis_price,book_id,dis_rate 