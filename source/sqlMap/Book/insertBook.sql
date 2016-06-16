insert into book(isbn,bookname,author,press,pubtime,booknature,dis_rate,status,createdate)
values(:isbn,:bookname,:author,:press,:pubtime,:booknature,:dis_rate,:status,getdate())