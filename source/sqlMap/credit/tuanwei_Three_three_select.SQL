select * from(select row_number() over(order by c.studentno) as row,recno,c.status,
c.studentno,s.name,c.year,c.term,convert(varchar(10),c.certficatetime,20) as certficatetime,projectname,c.credit,
convert(varchar(10),addtime,20) as addtime
from creditcount c inner join students s on s.studentno=c.studentno
INNER JOIN CLASSES ON s.classno = CLASSES.CLASSNO
where projectname like :projectname and subschool like :subschool
and credittype=:ctype and c.status like :status
and c.studentno like :studentno
and CLASSES.SCHOOL like :schoolno
) as b where b.row between :start and :end