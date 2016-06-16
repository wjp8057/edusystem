select row_number() over(order by ac.studentno) as row,ac.studentno,ac.name,ac.reason,ac.credit,convert(char(10),ac.date,20) as rd_time,ac.year,ac.term,ac.类型 as lx,
SCHOOLS.NAME as bs,ac.类别 as lb, convert(char(10),ac.certificatedate,20) as zssj,CLASSES.CLASSNAME as clsnm
from addcredit ac
inner join STUDENTS on STUDENTS.STUDENTNO = ac.studentno
left outer JOIN CLASSES on STUDENTS.CLASSNO = CLASSES.CLASSNO
LEFT OUTER JOIN SCHOOLS on CLASSES.SCHOOL = SCHOOLS.SCHOOL
where ac.studentno like :studentno and ac.reason like :projectname and ac.类型 like :lx and ac.类别 like :lb
and ac.year=:year and ac.term=:term
and CLASSES.SCHOOL like :schoolno
