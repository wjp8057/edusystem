insert into TestPlan(year,term,FLAG,DATE,COURSENO,ATTENDENTS,R10,rem,examType)
select dbo.getOne(S.YEAR),dbo.getOne(S.TERM),dbo.getOne(TC.FLAG),dbo.getOne(TB.TESTTIME),dbo.getOne(S.COURSENO+S.[GROUP]) as kh,dbo.getOne(renshu.rs),'','','B' from
SCORES S inner join (select courseno,[GROUP],count(*) as rs  from SCORES where YEAR=:YONE AND TERM=:TONE AND
 approach='D' group by COURSENO,[GROUP]) as renshu
on (S.COURSENO+S.[GROUP]=renshu.COURSENO+renshu.[GROUP])
inner join TESTCOURSE TC ON S.COURSENO=TC.COURSENO
inner join TESTBATCH TB ON TC.flag=TB.FLAG
where S.YEAR=:YTWO AND TERM=:TTWO AND S.APPROACH='D'
group by S.COURSENO,S.[GROUP]

