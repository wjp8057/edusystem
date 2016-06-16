select count(*) as ROWS from(select row_number() over(order by scores.studentno) as row,scores.studentno as xh,students.name as xm
from scores,students
where courseno+[group]=:courseno  and scores.studentno=students.studentno
      and year=:year and term=:term and scores.active=1
) as b where b.row BETWEEN :start and :end