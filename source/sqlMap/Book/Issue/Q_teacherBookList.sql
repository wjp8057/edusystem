select bookname,price,dis_price,dis_rate,count(*) number,author,press,book_id from teacherbook
where teacherno=:teacherno and [year]=:year and term=:term
group by bookname,author,press,price,dis_price,book_id,dis_rate