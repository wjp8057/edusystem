select  * from (select row_number() over(order by Courseno) as row,t.Totalwork_id,t.Courseno,t.Coursename,t.Standard,
t.code,ct.[VALUE] kclx,t.credit,SCHOOLS.NAME as school,t.mzks,ca.[VALUE] xklx,t.[group]
from totalwork  as t inner join COURSETYPEOPTIONS as ct on t.kclx=ct.NAME
inner join COURSEAPPROACHES as ca on t.xklx=ca.NAME
inner join SCHOOLS on t.SCHOOL=SCHOOLS.SCHOOL
where Courseno like :courseno and Coursename like :coursename and kclx like :kclx and xklx like :xklx
and t.school like :school and code like :code and status=0) as b where b.row between :start and :end