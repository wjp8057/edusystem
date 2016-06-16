select [year],term,bookname,author,press,price,dis_price,count(*) [count],sum(dis_price) [sum] from  STUDENTBOOK bs 
where bs.studentno=:STUDENTNO  
group by [year],term,bookname,author,press,price,dis_price,studentno,book_id 
order by year,term 