select classname,isbn,bookname,author,press,count(*) [count],price,dis_rate,dis_price, 
(price * count(*)) m_price,(dis_price * count(*)) s_price from studentbook 
where [year]=:year and term=:term and isbn like :isbn and bookname like :bookname 
group by classname,isbn,bookname,author,press,price,dis_rate,dis_price 