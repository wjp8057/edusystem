select count(*) as ROWS from(select row_number() over(order by TESTPLAN.COURSeNO) as row,
COURSES.COURSENAME as km,
TESTPLAN.COURSeNO as kh,
TESTPLAN.ROOMNO1 as kc1,
TESTPLAN.seats1  as kw1,
TESTPLAN.ROOMNO1 as kc2,
TESTPLAN.seats1  as kw2,
TESTPLAN.ROOMNO1 as kc3,
TESTPLAN.seats1  as kw3,
TESTPLAN.ATTENDENTS as xkrs,
监考教师1+','+监考教师2+','+监考教师3 as kc1jk,
监考教师4+','+监考教师5+','+监考教师6 as kc2jk,
监考教师7+','+监考教师8+','+监考教师9 as kc3jk
 from TESTPLAN inner join COURSES on courses.courseno=LEFT(TESTPLAN.COURSENO,7)
 WHERE year=:year and term=:term and examType='B'
 ) as b