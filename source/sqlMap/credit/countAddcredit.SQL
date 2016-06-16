select count(*) as ROWS from (select CLASSES.CLASSNAME as clsnm
from addcredit ac
inner join STUDENTS on STUDENTS.STUDENTNO = ac.studentno
left outer JOIN CLASSES on STUDENTS.CLASSNO = CLASSES.CLASSNO
where ac.studentno like :studentno and ac.reason like :projectname and ac.类型 like :lx and ac.类别 like :lb
and ac.year=:year and ac.term=:term
and CLASSES.SCHOOL like :schoolno
) as b