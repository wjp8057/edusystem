select count(*) as ROWS from(
select row_number() over(order by T.CourseNo) as row,
courseno kh,coursename kcmc,flag cc,
lock,courseno2 as djkh,
coursename as djkcm
from testcourse T
where T.CourseNo LIKE :COURSENO
) as b