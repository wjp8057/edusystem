select count(*) from book b left join bookprice p on b.book_id=p.book_id  
where b.isbn like :isbn and b.bookname like :bookname and b.booknature like :booknature 