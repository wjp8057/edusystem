select book_id,isbn,bookname,price,[year],term,count(*) num from studentbook
where [year]=:year and term=:term and isbn like :isbn and bookname like :bookname
GROUP BY book_id,isbn,bookname,price,[year],term order by isbn