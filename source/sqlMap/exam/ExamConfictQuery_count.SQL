select count(*) as ROWS from(select row_number() over(order by kssj) as row,kssj,jsh,jsxm,szxy,count(*) cfcs   from( select
temp.TESTTIME kssj,
teachers.teacherno jsh,
teachers.name jsxm,
schools.name szxy,
conflicts.roomno
from  (SELECT * from (
select flag,监考教师1 as teacherno,roomno1 as roomno,YEAR,term from testplan union all
select flag,监考教师1 as teacherno,roomno1 as roomno,YEAR,term from testplan union all
select flag,监考教师3 as teacherno,roomno1 as roomno,YEAR,term from testplan union all
select flag,监考教师4 as teacherno,roomno1 as roomno,YEAR,term from testplan union all
select flag,监考教师5 as teacherno,roomno1 as roomno,YEAR,term from testplan union all
select flag,监考教师6 as teacherno,roomno1 as roomno,YEAR,term from testplan union all
select flag,监考教师7 as teacherno,roomno3 as roomno,YEAR,term from testplan union all
select flag,监考教师8 as teacherno,roomno3 as roomno,YEAR,term from testplan union all
select flag,监考教师9 as teacherno,roomno3 as roomno,YEAR,term from testplan
) as b1 WHERE YEAR = :YONE and term  = :TONE)  as conflicts
inner join (select FLAG,TESTTIME from TESTBATCH where  year=:YTEN and term=:TTEN) as temp on temp.FLAG=conflicts.flag
inner join teachers on teachers.teacherno=conflicts.teacherno
inner join schools on schools.school=teachers.school
where teachers.teacherno is not null and teachers.teacherno not in ('000000','') and teachers.SCHOOL like :school
) as b2
group by kssj,jsh,jsxm,szxy,roomno
having count(*)>1
) as b3