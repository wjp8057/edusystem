SELECT book_student.[year] AS YEAR, book_student.term AS TERM, books.name AS BOOKSNAME,
      books.editor AS EDITOR, books.publish AS PUBLISH, books.price AS PRICE,
      books.rbprice AS RBPRICE, book_student.amount AS AMOUNT,
      books.rbprice * book_student.amount AS TOTALAMOUNT
FROM book_student INNER JOIN
      books ON book_student.bookMap = books.Recno
WHERE (book_student.studentno = :STUDENTNO)
ORDER BY YEAR,TERM