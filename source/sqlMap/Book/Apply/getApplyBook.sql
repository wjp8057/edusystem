select b.book_id,b.booknature,b.isbn,b.bookname,b.author,b.pubtime,b.press,b2.remarks,b2.apply_id 
from book b left join bookapply b2 on b.book_id=b2.book_id 
where b2.apply_id=:apply_id 