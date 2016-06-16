select book_id,isbn,bookname,author,p.name press,pubtime,booknature,dis_rate,b.status 
from book b left join bookpress p on b.press=p.id 
 where isbn like :isbn and bookname like :bookname and b.status like :status and booknature like :booknature order by book_id desc