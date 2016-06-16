INSERT INTO COURSEPLAN(SCHOOL, YEAR, TERM, COURSENO, [GROUP], WEEKS, ATTENDENTS,COURSETYPE, EXAMTYPE, CLASSNO)
SELECT SCHOOL, :yone AS Expr1, :tone AS Expr2, COURSENO, '00' AS Expr3,
      262143 AS Expr4, Limit, 'E' AS Expr5, 'E' AS Expr6, '000000' AS Expr7
FROM COURSES
WHERE (COURSENO LIKE '08%') AND (Quarter LIKE :zhong) AND (REM IS NULL or rem not like '%04%') AND
      (NOT EXISTS
          (SELECT *
         FROM courseplan
         WHERE year = :ytwo AND term = :ttwo AND courseplan.courseno = courses.courseno))
