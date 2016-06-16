select count(*) from teststudent  as table1
where courseno2 like (:COURSENO)+'%' and exists (
	select * from teststudent as table2 where table2.studentno=table1.studentno and flag=:FLAG
)