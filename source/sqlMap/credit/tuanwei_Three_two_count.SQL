select count(*) as ROWS from(select row_number() over(order by c.studentno) as row,c.studentno,s.name,c.year,c.term,c.certficatetime,projectname
from creditcount c inner join students s on s.studentno=c.studentno
where projectname like :projectname and subschool like :subschool
and credittype=:ctype and c.status like :status
and c.studentno like :studentno
) as b