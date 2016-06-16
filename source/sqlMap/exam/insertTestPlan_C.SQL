insert into TestPlan(year,term,FLAG,DATE,COURSENO,ATTENDENTS,R10,rem,examType)
select :YEAR,:TERM,T.FLAG,TB.TESTTIME,T.COURSENO,renshu.rs,'','','C'
FROM TestCourse as T inner join (select courseno,count(*) as rs from TestStudent group by courseno) as renshu on T.courseno=renshu.courseno
inner join TESTBATCH TB ON T.flag=TB.FLAG