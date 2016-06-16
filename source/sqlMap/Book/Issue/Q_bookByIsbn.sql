select b.*,p.price,p.dis_rate,p.dis_price from (select b.book_id,b.isbn,b.bookname,p.name press,b.author,max(pr.id) pId
from book b left join bookpress p on p.id=b.press left join bookprice pr on b.book_id=pr.book_id 
where b.status=0 and b.isbn like :isbn and pr.price is not null
group by b.book_id,b.isbn,b.bookname,p.name,b.author) b left join bookprice p on b.pId=p.id